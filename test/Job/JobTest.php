<?php

namespace ProcrastinatorTest\Job;

use PHPUnit\Framework\TestCase;
use Procrastinator\Job\Method;
use Procrastinator\Result;
use ProcrastinatorTest\Mock\TwoStage;

class JobTest extends TestCase
{
    public function test(): void
    {
        $job = new Method($this, "callMe");
        $result = $job->run();
        $this->assertEquals(Result::DONE, $result->getStatus());
    }

    public function testStateProperties(): void
    {
        $job = new Method($this, "callMe");

        $this->assertFalse($job->getStateProperty('testProp'));
        $this->assertEquals('bar', $job->getStateProperty('testProp', 'bar'));

        $job->setStateProperty('testProp', 'foo');
        $this->assertEquals('foo', $job->getStateProperty('testProp', 'bar'));
    }

    public function testError(): void
    {
        $job = new Method($this, "callError");
        $result = $job->run();
        $this->assertEquals(Result::ERROR, $result->getStatus());
        $this->assertEquals("I always fail", $result->getError());
    }

    public function testTimeLimit(): void
    {
        $timeLimit = 10;
        $job = new Method($this, "callError");
        $job->setTimeLimit($timeLimit);
        $job->run();
        $this->assertEquals($timeLimit, $job->getTimeLimit());
    }

    public function testReturn(): void
    {
        $job = new Method($this, "callReturn");
        $result = $job->run();
        $this->assertEquals(Result::DONE, $result->getStatus());
        $this->assertEquals("Hello", $result->getData());

        // Test that we do not execute when done.
        $result = $job->run();
        $this->assertEquals(Result::DONE, $result->getStatus());
        $this->assertEquals("Hello", $result->getData());
    }

    public function testTwoStage(): void
    {
        $job = new TwoStage();
        $result = $job->run();
        $this->assertEquals(Result::STOPPED, $result->getStatus());
        $this->assertEquals(json_encode(['a', 'b', 'c']), $result->getData());

        $result = $job->run();
        $this->assertEquals(Result::DONE, $result->getStatus());
        $this->assertEquals(json_encode(['a', 'b', 'c', 'd']), $result->getData());
    }

    public function callMe(): void
    {
    }

    /**
     * @return never
     */
    public function callError()
    {
        throw new \Exception("I always fail");
    }

    public function callReturn(): string
    {
        return "Hello";
    }
}
