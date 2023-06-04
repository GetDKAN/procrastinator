<?php

namespace ProcrastinatorTest;

use PHPUnit\Framework\TestCase;
use Procrastinator\Result;

class ResultTest extends TestCase
{
    public function test(): void
    {
        $this->expectExceptionMessage("Invalid status blah");
        $result = new Result();
        $result->setStatus("blah");
    }

    public function testSerialization(): void
    {
        $result = new Result();
        $result->setStatus(Result::ERROR);
        $result->setData("Hello Friend");
        $json = json_encode($result);

        $result2 = Result::hydrate($json);

        $this->assertEquals($result, $result2);
    }
}
