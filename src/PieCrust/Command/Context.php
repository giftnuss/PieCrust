<?php

namespace PieCrust\Command;

use \Exception;
use \Console_CommandLine;
use \Console_CommandLine_Result;
use PieCrust\IPieCrust;
use PieCrust\PieCrustException;


/*
 * The context for `piecrust` command.
 */
class Context
{
    protected $app;
    /**
     * Gets the PieCrust app related to the command being run.
     */
    public function getApp()
    {
        return $this->app;
    }

    protected $result;
    /**
     * Returns the parser result.
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Returns the logger.
     */
    public function getLog()
    {
        return $this->getApp()->getEnvironment()->getLog();
    }

    protected $debuggingEnabled;
    /**
     * Returns whether the command should print debug messages.
     */
    public function isDebuggingEnabled()
    {
        return $this->debuggingEnabled;
    }

    public function __construct(IPieCrust $pieCrust, Console_CommandLine_Result $result)
    {
        $this->app = $pieCrust;
        $this->result = $result;
        $this->debuggingEnabled = false;
    }
}

