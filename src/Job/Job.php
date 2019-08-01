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
        } catch (\Exception $e) {
            $this->result->setStatus(Result::ERROR);
            $this->result->setError($e->getMessage());
            return $this->result;
        }

        if ($data) {
            if ($data instanceof Result) {
                $this->result = $data;
            } elseif (is_string($data)) {
                $this->result->setData($data);
                $this->result->setStatus(Result::DONE);
            } else {
                throw new \Exception("Invalid result or data format.");
            }
        } else {
            $this->result->setStatus(Result::DONE);
        }

        return $this->result;
    }

    abstract protected function runIt();

    public function setTimeLimit(int $seconds)
    {
        $this->timeLimit = $seconds;
    }

    public function unsetTimeLimit()
    {
        $this->timeLimit = null;
    }

    public function getState()
    {
        return (array) json_decode($this->getResult()->getData());
    }

    public function getStateProperty($property)
    {
        return $this->getState()[$property];
    }

    public function getResult(): Result
    {
        return $this->result;
    }

    private function setState($state)
    {
        $this->getResult()->setData(json_encode($state));
    }

    public function setStateProperty($property, $value)
    {
        $state = $this->getState();
        $state[$property] = $value;
        $this->setState($state);
    }

    public function jsonSerialize()
    {
        return (object) [
            'timeLimit' => $this->timeLimit,
            'result' => $this->getResult()->jsonSerialize()
        ];
    }

    /**
     * Hydrate an object from the json created by jsonSerialize().
     * You will want to override this method when implementing specific jobs.
     * You can use this function for the initial JSON decoding by calling
     * parent::hydrate() in your implementation.
     *
     * @param string $json
     *   JSON string used to hydrate a new instance of the class.
     */
    public static function hydrate($json) {
        $data = json_decode($json);
        return $data;
    }
}
