<?php


namespace Procrastinator;

/**
 * @todo Change name to HydratableTrait.
 */
trait HydratableTrait
{
    public static function hydrate(string $json)
    {
        $data = json_decode($json);

        $reflector = new \ReflectionClass(self::class);
        $object = $reflector->newInstanceWithoutConstructor();

        $reflector = new \ReflectionClass($object);
        foreach ($data as $property => $value) {
            $p = $reflector->getProperty($property);
            $p->setAccessible(true);
            $p->setValue($object, $value);
        }
        return $object;
    }
}
