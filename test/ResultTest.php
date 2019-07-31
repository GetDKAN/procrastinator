<?php

namespace ProcrastinatorTest;

use Procrastinator\Result;

class ResultTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->expectExceptionMessage("Invalid status blah");
        $result = new Result();
        $result->setStatus("blah");
    }

    public function testSerialization()
    {
        $result = new Result();
        $result->setStatus(Result::ERROR);
        $result->setData("Hello Friend");
        $json = json_encode($result);

        $result2 = Result::hydrate($json);

        $this->assertEquals($result, $result2);
    }
}
