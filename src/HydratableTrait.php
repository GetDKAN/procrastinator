<?php

namespace Procrastinator;

trait HydratableTrait
{
    public static function hydrate(string $json, $instance = null)
    {
        $data = self::decode($json);

        $class = new \ReflectionClass(static::class);

        if (!$instance) {
            $instance = $class->newInstanceWithoutConstructor();
        }

        $properties = [];
        $parent = $class;
        while ($parent) {
            $properties = array_merge($properties, $parent->getProperties());
            $parent = $parent->getParentClass();
        }

        /* @var $property \ReflectionProperty */
        foreach ($properties as $property) {
            $name = $property->getName();
            if (isset($data[$name])) {
                $property->setAccessible(true);
                $property->setValue($instance, static::hydrateProcessValue($data[$name]));
            }
        }
        return $instance;
    }

    private static function decode($json)
    {
        $data = (array) json_decode($json);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception(json_last_error_msg());
        }
        return $data;
    }

    private static function hydrateProcessValue($value)
    {
        if (is_object($value)) {
            $value = (array) $value;
            if (isset($value["@type"])) {
                $type = $value["@type"];
                $value = (object) $value;
                switch ($type) {
                    case "object":
                        return static::hydrateProcessValueObject($value);
                    case "array":
                        return static::hydrateProcessValueArray($value);
                }
                throw new \Exception("Unrecognized type: {$type}.");
            } else {
                return (object) $value;
            }
        }
        return $value;
    }

    private static function hydrateProcessValueObject($value)
    {
        $value = (array) $value;
        $class = $value['@class'];
        return $class::hydrate(json_encode($value['data']));
    }

    private static function hydrateProcessValueArray($value)
    {
        $value = (array) $value;
        $array = (array) $value['data'];
        return array_map(function ($item) {
            return static::hydrateProcessValue($item);
        }, $array);
    }
}
