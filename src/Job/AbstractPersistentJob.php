<?php

namespace Procrastinator\Job;

use Contracts\StorerInterface;
use Contracts\RetrieverInterface;
use Contracts\HydratableInterface;

abstract class AbstractPersistentJob extends Job implements HydratableInterface
{
    private $identifier;
    private $storage;

    public static function get(string $identifier, $storage) {
        if ($storage instanceof StorerInterface && $storage instanceof RetrieverInterface) {
            $job = $storage->retrieve($identifier);
            if ($job) {
                return $job;
            }

            $new = new static($identifier, $storage);
            $storage->store($new, $identifier);
            return $new;
        }
        return FALSE;
    }

    private function __construct(string $identifier, $storage)
    {
        $this->identifier = $identifier;
        $this->storage = $storage;
    }

    public function setTimeLimit(int $seconds): bool
    {
        $return = parent::setTimeLimit($seconds);
        $this->storage->store($this, $this->identifier);
        return $return;
    }

    protected function setStatus($status) {
        parent::setStatus($status);
        $this->storage->store($this, $this->identifier);
    }

    protected function setError($message)
    {
        parent::setError($message);
        $this->storage->store($this, $this->identifier);
    }

    protected function setState($state)
    {
        parent::setState($state);
        $this->storage->store($this, $this->identifier);
    }

    public function jsonSerialize()
    {
        $object = parent::jsonSerialize();
        $object->identifier = $this->identifier;
        return $object;
    }
}