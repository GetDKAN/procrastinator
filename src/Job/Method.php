<?php


namespace Procrastinator\Job;

use Procrastinator\Result;

class Method extends Job
{
    private $object;
    private $methodName;

    public function __construct($object, $methodName)
    {
        parent::__construct();
        $this->object = $object;
        $this->methodName = $methodName;
    }

    protected function runIt()
    {
        return call_user_func([$this->object, $this->methodName]);
    }

    public static function hydrate($json): Method
    {
        $data = parent::hydrate($json);

        $reflector = new \ReflectionClass(self::class);
        $object = $reflector->newInstanceWithoutConstructor();

        $reflector = new \ReflectionClass($object);

        $p = $reflector->getParentClass()->getProperty('timeLimit');
        $p->setAccessible(true);
        $p->setValue($object, $data->timeLimit);

        $p = $reflector->getParentClass()->getProperty('result');
        $p->setAccessible(true);
        $p->setValue($object, Result::hydrate(json_encode($data->result)));

        return $object;
    }
}
