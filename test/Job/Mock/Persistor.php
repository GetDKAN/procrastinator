<?php


namespace ProcrastinatorTest\Job\Mock;

use Procrastinator\Job\AbstractPersistentJob;
use Procrastinator\Result;

class Persistor extends AbstractPersistentJob
{
    private $errorOut = false;

    public static function hydrate(string $json, $instance = null)
    {
        $class = new \ReflectionClass(self::class);

        if (!$instance) {
            $instance = $class->newInstanceWithoutConstructor();
        }

        $object = json_decode($json);

        $job = $class->getParentClass()->getParentClass();

        $result = $job->getProperty("result");
        $result->setAccessible(true);
        $result->setValue($instance, Result::hydrate(json_encode($object->result)));

        $timeLimit = $job->getProperty("timeLimit");
        $timeLimit->setAccessible(true);
        $timeLimit->setValue($instance, $object->timeLimit);

        $persistentJob = $class->getParentClass();

        $identifier = $persistentJob->getProperty("identifier");
        $identifier->setAccessible(true);
        $identifier->setValue($instance, $object->identifier);

        return $instance;
    }

    public function errorOut()
    {
        $this->errorOut = true;
    }

    protected function runIt()
    {
        if ($this->errorOut) {
            throw new \Exception("ERROR");
        }
        $this->setStateProperty("ran", true);
        return;
    }
}
