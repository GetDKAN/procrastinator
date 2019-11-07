<?php


namespace Procrastinator;


trait JsonSerializeTrait
{
    private function serialize()
    {
        $serialized = [];

        $properties = [];
        $class = new \ReflectionClass(static::class);
        $parent = $class;
        while ($parent) {
            $properties = array_merge($properties, $parent->getProperties());
            $parent = $parent->getParentClass();
        }

        /* @var $property \ReflectionProperty */
        foreach ($properties as $property) {
            $name = $property->getName();
            if (!in_array($name, $this->serializeIgnoreProperties())) {
                $property->setAccessible(true);
                $serialized[$property->getName()] = $this->serializeProcessValue($property->getValue($this));
            }
        }

        return $serialized;
    }

    private function serializeProcessValue($value)
    {
        if (is_object($value)) {
            return $this->serializeProcessValueObject($value);
        } elseif (is_array($value)) {
            return $this->serializeProcessValueArray($value);
        }
        return $value;
    }

    private function serializeProcessValueObject($object)
    {
        if ($object instanceof \stdClass) {
            return $object;
        } elseif ($object instanceof \JsonSerializable) {
            return ['@type' => 'object', '@class' => get_class($object), 'data' => $object->jsonSerialize()];
        } else {
            throw new \Exception("Failed to serialize object of class {get_class($object)} as it does not implment \\JsonSerializable.");
        }
    }

    private function serializeProcessValueArray($array)
    {
        $serialized = [];

        foreach ($array as $key => $value) {
            $serialized[$key] = $this->serializeProcessValue($value);
        }

        return ['@type' => 'array', 'data' => $serialized];
    }

    protected function serializeIgnoreProperties(): array
    {
        return [];
    }
}
