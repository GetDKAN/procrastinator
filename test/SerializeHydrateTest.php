<?php

namespace ProcrastinatorTest;

use PHPUnit\Framework\TestCase;
use ProcrastinatorTest\Mock\Complex;

class SerializeHydrateTest extends TestCase
{
    public function test()
    {
        $object = new Complex();
        $json = json_encode($object, JSON_THROW_ON_ERROR);

        $object2 = Complex::hydrate($json);

        $hello = $object2->getItem('hello');
        $this->assertTrue(is_object($hello));
        $this->assertEquals('Gerardo', $hello->first_name);
        $this->assertEquals('Gonzalez', $hello->last_name);
        $this->assertEquals(2, $object2->getItem("goodbye"));
    }
}
