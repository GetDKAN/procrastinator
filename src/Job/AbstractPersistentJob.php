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
            $new = new static($identifier, $storage);

            $json = $storage->retrieve($identifier);
            if ($json) {
                return static::hydrate($json, $new);
            }

            $storage->store(json_encode($new), $identifier);
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
        $this->selfStore();
        return $return;
    }

    public function jsonSerialize()
    {
        $object = parent::jsonSerialize();
        $object->identifier = $this->identifier;
        return $object;
    }

    protected function setStatus($status) {
        parent::setStatus($status);
        $this->selfStore();
    }

    protected function setError($message)
    {
        parent::setError($message);
        $this->selfStore();
    }

    protected function setState($state)
    {
        parent::setState($state);
        $this->selfStore();
    }

    private function selfStore() {
        $this->storage->store(json_encode($this), $this->identifier);
    }
}