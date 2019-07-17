<?php


namespace Procrastinator;


trait Hydratable
{
  public static function hydrate($json) {
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