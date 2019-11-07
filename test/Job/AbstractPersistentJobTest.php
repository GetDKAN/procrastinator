<?php

namespace ProcrastinatorTest\Job;

use Contracts\Mock\Storage\Memory;
use PHPUnit\Framework\TestCase;
use ProcrastinatorTest\Mock\Persistor;

class AbstractPersistentJobTest extends TestCase
{
    public function testSerialization()
    {
        $storage = new Memory();

        $timeLimit = 10;
        $job = Persistor::get("1", $storage);
        $job->setStateProperty("ran", false);

        $job->setTimeLimit($timeLimit);
        $job->run();

        $json = json_encode($job);

        /* @var $job2 \Procrastinator\Job\AbstractPersistentJob */
        $job2 = Persistor::hydrate($json);

        $data = json_decode($job2->getResult()->getData());
        $this->assertEquals(true, $data->ran);
        $this->assertEquals($timeLimit, $job2->getTimeLimit());

        $job3 = Persistor::get("1", $storage);

        $data = json_decode($job3->getResult()->getData());
        $this->assertEquals(true, $data->ran);
        $this->assertEquals(true, $job3->getStateProperty("ran"));
        $this->assertEquals(true, $job3->getStateProperty("ran2", true));
        $this->assertEquals($timeLimit, $job3->getTimeLimit());
    }

    public function testBadStorage()
    {
        $this->assertFalse(Persistor::get("1", new class {
        }));
    }

    public function testJobError()
    {
        $storage = new Memory();

        $timeLimit = 10;
        $job = Persistor::get("1", $storage);
        $job->errorOut();

        $job->setTimeLimit($timeLimit);
        $job->run();

        $this->assertEquals("ERROR", $job->getResult()->getError());

        $job2 = Persistor::get("1", $storage);
        $job2->run();
        $this->assertEquals(true, $job2->getStateProperty("ran"));
    }
}
