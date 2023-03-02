<?php

namespace ProcrastinatorTest\Mock;

use Procrastinator\Job\Job;
use Procrastinator\Result;

class TwoStage extends Job
{
    private int $stage = 1;

    protected function runIt()
    {
        if ($this->stage == 1) {
            $this->stage = 2;

            $result = $this->getResult();
            $result->setStatus(Result::STOPPED);
            $result->setData(json_encode(['a', 'b', 'c']));

            return $result;
        } elseif ($this->stage == 2) {
            $data_string = $this->getResult()->getData();
            $data = json_decode($data_string);
            $data[] = 'd';

            return json_encode($data);
        }
    }
}
