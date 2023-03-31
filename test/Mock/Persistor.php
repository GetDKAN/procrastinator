<?php


namespace ProcrastinatorTest\Mock;

use Procrastinator\Job\AbstractPersistentJob;

class Persistor extends AbstractPersistentJob
{
    private bool $errorOut = false;

    public function errorOut()
    {
        $this->errorOut = true;
    }

    protected function runIt()
    {
        if ($this->errorOut) {
            throw new \Exception("ERROR");
        }
        $this->setStateProperty("ran", true);
        return;
    }

    protected function serializeIgnoreProperties(): array
    {
        $properties = parent::serializeIgnoreProperties();
        $properties[] = 'errorOut';
        return $properties;
    }
}
