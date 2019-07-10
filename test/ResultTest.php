<?php

use Procrastinator\Result;

class ResultTest extends \PHPUnit\Framework\TestCase
{
  public function test() {
    $this->expectExceptionMessage("Invalid status blah");
    $result = new Result();
    $result->setStatus("blah");
  }
}