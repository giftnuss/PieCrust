<?php

namespace PieCrust\Command\Commands;

use \Console_CommandLine;
use PieCrust\IPieCrust;
use PieCrust\Command\Context;


/**
 * The extension to a command, most of the time a sub-command.
 */
abstract class CommandExtension
{
    /**
     * Gets the name of the extension.
     */
    public abstract function getName();

    /**
     * Extends or modifies the command's parser.
     */
    public abstract function setupParser(Console_CommandLine $parser, IPieCrust $pieCrust);

    /**
     * Runs the command extension.
     */
    public abstract function run(Context $context);
}

