<?php

namespace PieCrust\Command\Commands;

use \Console_CommandLine;
use PieCrust\IPieCrust;
use PieCrust\PieCrustException;
use PieCrust\Command\Context;
use PieCrust\Command\Environment;


class PrepareCommand extends Command
{
    public function getName()
    {
        return 'prepare';
    }

    public function setupParser(Console_CommandLine $parser, IPieCrust $pieCrust)
    {
        $parser->description = "Helps with the creation of content in the website.";

        $environment = $pieCrust->getEnvironment();
        if ($environment instanceof Environment)
        {
            $extensions = $environment->getCommandExtensions($this->getName());
            foreach ($extensions as $ext)
            {
                $extensionParser = $parser->addCommand($ext->getName());
                $ext->setupParser($extensionParser, $pieCrust);
            }
        }
    }

    public function run(Context $context)
    {
        $app = $context->getApp();
        $result = $context->getResult();
        $log = $context->getLog();

        $environment = $app->getEnvironment();
        if (!($environment instanceof Environment))
            throw new PieCrustException(
                "Can't run the `prepare` command without a command environment.");

        $extensionName = $result->command->command_name;
        $extensions = $environment->getCommandExtensions($this->getName());
        foreach ($extensions as $ext)
        {
            if ($ext->getName() == $extensionName)
            {
                $ext->run($context);
                return;
            }
        }
        throw new PieCrustException("No such extension for the `prepare` command: " . $extensionName);
    }
}

