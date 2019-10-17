<?php

namespace ProcrastinatorTest\Job;

use Contracts\HydratableInterface;
use Contracts\Mock\Storage\Memory;
use PHPUnit\Framework\TestCase;
use Procrastinator\Job\AbstractPersistentJob;
use Procrastinator\Result;

class AbstractPersistentJobTest extends TestCase
{
    public function testSerialization()
    {
        $storage = new Memory();

        $timeLimit = 10;
        $job = PersistorTest::get("1", $storage);
        $job->setStateProperty("ran", false);

        $job->setTimeLimit($timeLimit);
        $job->run();

        $json = json_encode($job);

        /* @var $job2 \Procrastinator\Job\AbstractPersistentJob */
        $job2 = PersistorTest::hydrate($json);

        $data = json_decode($job2->getResult()->getData());
        $this->assertEquals(true, $data->ran);
        $this->assertEquals($timeLimit, $job2->getTimeLimit());

        $job3 = PersistorTest::get("1", $storage);

        $data = json_decode($job3->getResult()->getData());
        $this->assertEquals(true, $data->ran);
        $this->assertEquals(true, $job3->getStateProperty("ran"));
        $this->assertEquals(true, $job3->getStateProperty("ran2", true));
        $this->assertEquals($timeLimit, $job3->getTimeLimit());
    }

    public function testBadStorage()
    {
        $this->assertFalse(PersistorTest::get("1", new BadStorageTest()));
    }

    public function testJobError()
    {
        $storage = new Memory();

        $timeLimit = 10;
        $job = PersistorTest::get("1", $storage);
        $job->errorOut();

        $job->setTimeLimit($timeLimit);
        $job->run();

        $this->assertEquals("ERROR", $job->getResult()->getError());

        $job2 = PersistorTest::get("1", $storage);
        $job2->run();
        $this->assertEquals(true, $job2->getStateProperty("ran"));
    }
}

class PersistorTest extends AbstractPersistentJob
{
    private $errorOut = false;

    public static function hydrate(string $json, $instance = null)
    {
        $class = new \ReflectionClass(PersistorTest::class);

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

class ObjectMemoryTest extends Memory
{
    private $class;

    public function __construct($class)
    {
        $this->class = $class;
    }

    public function store($data, string $id = null): string
    {
        if ($data instanceof HydratableInterface) {
            return parent::store(json_encode($data), $id);
        }
        throw new \Exception();
    }

    public function retrieve(string $id)
    {
        $data = parent::retrieve($id);
        if ($data) {
            return $this->class::hydrate($data);
        }
        return null;
    }
}

class BadStorageTest
{

}
