<?php

namespace PieCrust\Command;

use Logger;
use PieCrust\Environment\CachedEnvironment;


/**
 * An environment that runs under a `Chef` instance in
 * command line.
 */
class Environment extends CachedEnvironment
{
    protected $commandExtensions;
    /**
     * Gets the command extensions.
     */
    public function getCommandExtensions($commandName)
    {
        if (isset($this->commandExtensions[$commandName]))
            return $this->commandExtensions[$commandName];
        return array();
    }

    /**
     * Adds a command extension.
     */
    public function addCommandExtension($commandName, $extension)
    {
        if (!isset($this->commandExtensions[$commandName]))
            $this->commandExtensions[$commandName] = array();

        $this->commandExtensions[$commandName][] = $extension;
    }

    /**
     * Creates a new instance of command `environment`.
     */
    public function __construct($logger=null)
    {
        if($logger === null) {
            $logger = Logger::getLogger('piecrust');
        }
        parent::__construct($logger);
    }
}

