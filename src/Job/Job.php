<?php


namespace Procrastinator\Job;

use Procrastinator\Result;

abstract class Job implements IJob
{
  private $result;

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

  public function getResult(): Result
  {
    return $this->result;
  }
}