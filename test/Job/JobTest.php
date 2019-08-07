<?php

namespace ProcrastinatorTest\Job;

use Procrastinator\Job\Job;
use Procrastinator\Job\Method;
use Procrastinator\Result;

class RunnerTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $job = new Method($this, "callMe");
        $result = $job->run();
        $this->assertEquals(Result::DONE, $result->getStatus());
    }

    public function testStateProperties()
    {
        $job = new Method($this, "callMe");

        $this->assertFalse($job->getStateProperty('testProp'));
        $this->assertEquals('bar', $job->getStateProperty('testProp', 'bar'));

        $job->setStateProperty('testProp', 'foo');
        $this->assertEquals('foo', $job->getStateProperty('testProp', 'bar'));
    }

    public function testError()
    {
        $job = new Method($this, "callError");
        $result = $job->run();
        $this->assertEquals(Result::ERROR, $result->getStatus());
        $this->assertEquals("I always fail", $result->getError());
    }

    public function testTimeLimit()
    {
        $timeLimit = 10;
        $job = new Method($this, "callError");
        $job->setTimeLimit($timeLimit);
        $result = $job->run();
        $this->assertEquals($timeLimit, $job->getTimeLimit());

        $job->unsetTimeLimit();
        $this->assertEquals(null, $job->getTimeLimit());
    }

    public function testReturn()
    {
        $job = new Method($this, "callReturn");
        $result = $job->run();
        $this->assertEquals(Result::DONE, $result->getStatus());
        $this->assertEquals("Hello", $result->getData());
    }

    public function testTwoStage()
    {
        $job = new TwoStage();
        $result = $job->run();
        $this->assertEquals(Result::STOPPED, $result->getStatus());
        $this->assertEquals(json_encode(['a', 'b', 'c']), $result->getData());

        $result = $job->run();
        $this->assertEquals(Result::DONE, $result->getStatus());
        $this->assertEquals(json_encode(['a', 'b', 'c', 'd']), $result->getData());
    }

    public function testSerialization()
    {
        $statePropertyA = 1;
        $statePropertyB = 2;
        $timeLimit = 10;

        $job = new Method($this, "callMe");
        $job->setTimeLimit($timeLimit);
        $job->setStateProperty('a', $statePropertyA);
        $job->setStateProperty('b', $statePropertyB);
        $job->run();

        $json = json_encode($job->jsonSerialize());

        $job2 = Method::hydrate($json);

        $this->assertEquals($statePropertyA, $job2->getStateProperty('a'));
        $this->assertEquals($statePropertyB, $job2->getStateProperty('b'));

        $this->assertEquals($timeLimit, $job2->getTimeLimit());
    }

    public function callMe()
    {
    }

    public function callError()
    {
        throw new \Exception("I always fail");
    }

    public function callReturn()
    {
        return "Hello";
    }
}
