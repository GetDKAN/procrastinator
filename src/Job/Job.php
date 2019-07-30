<?php


namespace Procrastinator\Job;

use Procrastinator\Result;

abstract class Job implements \JsonSerializable
{
  private $result;
  private $timeLimit;

  public function __construct()
  {
    $this->result = new Result(Result::STOPPED);
  }

  public function run(): Result
  {
    $this->result->setStatus(Result::IN_PROGRESS);

    try {
      $data = $this->runIt();
    }
    catch (\Exception $e) {
      $this->result->setStatus(Result::ERROR);
      $this->result->setError($e->getMessage());
      return $this->result;
    }

    if ($data) {
      if ($data instanceof Result) {
        $this->result = $data;
      }
      else if (is_string($data)) {
        $this->result->setData($data);
        $this->result->setStatus(Result::DONE);
      }
      else {
        throw new \Exception("Invalid result or data format.");
      }
    }
    else {
      $this->result->setStatus(Result::DONE);
    }

    return $this->result;
  }

  abstract protected function runIt();

  public function setTimeLimit(int $seconds)
  {
    $this->timeLimit = $seconds;
  }

  public function unsetTimeLimit() {
    unset($this->timeLimit);
  }

  public function getState() {
    return (array) json_decode($this->getResult()->getData());
  }

  public function getStateProperty($property) {
    return $this->getState()[$property];
  }

  public function getResult(): Result
  {
    return $this->result;
  }

  private function setState($state) {
    $this->getResult()->setData(json_encode($state));
  }

  public function setStateProperty($property, $value) {
    $state = $this->getState();
    $state[$property] = $value;
    $this->setState($state);
  }

  public function jsonSerialize()
  {
    return (object) ['timeLimit' => $this->timeLimit, 'result' => $this->getResult()];
  }

  public static function hydrate($json) {
    $data = json_decode($json);

    $reflector = new \ReflectionClass(self::class);
    $object = $reflector->newInstanceWithoutConstructor();

    $reflector = new \ReflectionClass($object);

    $p = $reflector->getProperty('timeLimit');
    $p->setAccessible(true);
    $p->setValue($object, $data->timeLimit);

    $class = $reflector->getParentClass();
    $p = $class->getProperty('result');
    $p->setAccessible(true);
    $p->setValue($object, Result::hydrate(json_encode($data->result)));

    return $object;
  }
}