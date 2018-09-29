<?php

namespace PieCrust\Baker\Processors;

use PieCrust\IPieCrust;
use PieCrust\PieCrustException;
use PieCrust\Baker\IBaker;


abstract class BaseProcessor
{
    public function initialize(IPieCrust $pieCrust)
    {
        $this->pieCrust = $pieCrust;
    }

    public function getLog()
    {
        return $this->pieCrust->getEnvironment()->getLog();
    }
}
