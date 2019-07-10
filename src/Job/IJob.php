<?php


namespace Procrastinator\Job;

use Procrastinator\Result;

interface IJob
{
  public function run(): Result;
}