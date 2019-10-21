<?php

namespace Procrastinator;

use Contracts\HydratableInterface;

class Result implements HydratableInterface
{
    use HydratableTrait;

    const STOPPED = 'stopped';
    const IN_PROGRESS = 'in_progress';
    const ERROR = 'error';
    const DONE = 'done';

    private $status = self::STOPPED;
    private $data = "";
    private $error = "";

    public function setStatus($status)
    {
        $statuss = [self::STOPPED, self::IN_PROGRESS, self::ERROR, self::DONE];
        if (in_array($status, $statuss)) {
            $this->status = $status;
        } else {
            throw new \Exception("Invalid status {$status}");
        }
    }

    public function setData(string $data)
    {
        $this->data = $data;
    }

    public function setError(string $error)
    {
        $this->error = $error;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getError()
    {
        return $this->error;
    }

    public function jsonSerialize()
    {
        return (object) ['status' => $this->status, 'data' => $this->data, 'error' => $this->error];
    }
}
