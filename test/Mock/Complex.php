<?php

namespace ProcrastinatorTest\Mock;

use Contracts\HydratableInterface;
use Procrastinator\HydratableTrait;
use Procrastinator\JsonSerializeTrait;

class Complex implements \JsonSerializable, HydratableInterface
{
    use JsonSerializeTrait;
    use HydratableTrait;

    private $stuff;

    public function __construct()
    {
        $this->stuff["hello"] = (object) [
            "first_name" => "Gerardo",
            "last_name" => "Gonzalez"
        ];
        $this->stuff['goodbye'] = 2;
    }

    public function getItem($key)
    {
        return $this->stuff[$key];
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->serialize();
    }
}
