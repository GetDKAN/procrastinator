<?php


namespace Procrastinator\Jobs;

use Procrastinator\Job\Job;
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


}