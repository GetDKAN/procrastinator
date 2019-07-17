<?php

use Procrastinator\Job\Job;
use Procrastinator\Job\Method;
use Procrastinator\Result;

class RunnerTest extends \PHPUnit\Framework\TestCase
{
  public function test() {
    $job = new Method($this, "callMe");
    $result = $job->run();
    $this->assertEquals(Result::DONE, $result->getStatus());
  }

  public function testError() {
    $job = new Method($this, "callError");
    $result = $job->run();
    $this->assertEquals(Result::ERROR, $result->getStatus());
    $this->assertEquals("I always fail", $result->getError());
  }

  public function testReturn() {
    $job = new Method($this, "callReturn");
    $result = $job->run();
    $this->assertEquals(Result::DONE, $result->getStatus());
    $this->assertEquals("Hello", $result->getData());
  }

  public function testTwoStage() {
    $job = new TwoStage();
    $result = $job->run();
    $this->assertEquals(Result::STOPPED, $result->getStatus());
    $this->assertEquals(json_encode(['a', 'b', 'c']), $result->getData());

    $result = $job->run();
    $this->assertEquals(Result::DONE, $result->getStatus());
    $this->assertEquals(json_encode(['a', 'b', 'c', 'd']), $result->getData());
  }

  public function callMe() {
  }

  public function callError() {
    throw new \Exception("I always fail");
  }

  public function callReturn() {
    return "Hello";
  }
}

class TwoStage extends Job {
  private $stage = 1;

  protected function runIt()
  {
    if ($this->stage == 1) {
      $this->stage = 2;

      $result = $this->getResult();
      $result->setStatus(Result::STOPPED);
      $result->setData(json_encode(['a', 'b', 'c']));

      return $result;
    }
    else if ($this->stage == 2) {
      $data_string = $this->getResult()->getData();
      $data = json_decode($data_string);
      $data[] = 'd';

      return json_encode($data);
    }
  }

}