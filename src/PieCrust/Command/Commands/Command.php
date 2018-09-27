<?php

namespace PieCrust\Command\Commands;

use \Console_CommandLine;
use \Console_CommandLine_Result;
use PieCrust\IPieCrust;
use PieCrust\Command\Context;
use PieCrust\Command\Environment;


/**
 * The interface for piecrust command.
 */
abstract class Command
{
    /**
     * Gets the name of the command.
     */
    public abstract function getName();

    /**
     * Gets whether this command requires an actual website to run.
     */
    public function requiresWebsite()
    {
        return true;
    }

    /**
     * Creates the command's sub-parser.
     */
    public abstract function setupParser(Console_CommandLine $parser, IPieCrust $pieCrust);

    /**
     * Runs the command.
     */
    public abstract function run(Context $context);
}
