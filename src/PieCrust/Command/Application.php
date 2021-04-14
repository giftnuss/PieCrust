<?php

namespace PieCrust\Command;

/**
 * The piecrust command line application.
 */
class Application
{
    /**
     * The command
     */
    protected $command;

    /**
     * Builds a new instance of piecrust command.
     */
    public function __construct()
    {
        $this->command = new Command;
    }

    /**
     * Prepare default options and commands.
     */
    public function setup()
    {
        foreach($this->getOptions() as $opt) {
            $this->registerOption($opt);
        }
        $this->registerCommand('PieCrust\\Command\\Bake');
        $this->registerCommand('PieCrust\\Command\\Prepare');
    }

    public function registerOption($opt)
    {
        $this->command->registerOption($opt);
    }

    public function registerCommand($namespace)
    {
        $this->command->registerCommand($namespace);
    }

    public function run ($args)
    {
        $this->command->run($args);
    }





    /**
     * Runs piecrust given some command-line arguments.
     */
    public function setupX()
    {
        foreach($this->getOptions() as $opt) {
            $this->registerOption($opt);
        }
        $this->registerCommand('PieCrust\\Command\\Bake');

/*
        $this->getopt->process($arguments);
*/
        return;

        // Find if whether the `--root` or `--config` parameters were given.
        $rootDir = null;
        $isThemeSite = false;
        $configVariant = null;
        for ($i = 1; $i < count($userArgv); ++$i) {
            $arg = $userArgv[$i];

            if (substr($arg, 0, strlen('--root=')) == '--root=') {
                $rootDir = substr($arg, strlen('--root='));

            }
            elseif ($arg == '--root') {
                $rootDir = $userArgv[$i + 1];
                ++$i;
            }
            elseif (substr($arg, 0, strlen('--config=')) == '--config=') {
                $configVariant = substr($arg, strlen('--config='));
            }
            elseif ($arg == '--config') {
                $configVariant = $userArgv[$i + 1];
                ++$i;
            }
            elseif ($arg == '--theme') {
                $isThemeSite = true;
            }
            else if ($arg[0] != '-') {
                // End of the global arguments sections. This is
                // the command name.
                break;
            }
        }
        if ($rootDir == null) {
            // No root given. Find it ourselves.

        }
        else {
            // The root was given.
            $rootDir = trim($rootDir, " \"");
            if (!is_dir($rootDir)) {
                throw new PieCrustException("The given root directory doesn't exist: " . $rootDir);
            }
        }

        // Build the appropriate app.
        if ($rootDir == null) {
            throw new PieCrustException("There was no root dir given.");
        }
        else {
            $environment = new Environment();
            $pieCrust = new PieCrust(array(
                'root' => $rootDir,
                'cache' => !in_array('--no-cache', $userArgv),
                'environment' => $environment,
                'theme_site' => $isThemeSite
            ));
        }

        // Pre-load the correct config variant if any was specified.
        if ($configVariant != null) {
            // You can't apply a config variant if there's no website.
            if ($rootDir == null)
            {
                $cwd = getcwd();
                throw new PieCrustException("No PieCrust website in '{$cwd}' ('_content/config.yml' not found!).");
            }

            $configVariant = trim($configVariant, " \"");
            $pieCrust->getConfig()->applyVariant('variants/' . $configVariant);
        }

        // Set up the command line parser.
        $parser = new \Console_CommandLine(array(
            'name' => 'piecrust',
            'description' => 'The piecrust command manages your website.',
            'version' => PieCrustDefaults::VERSION
        ));
        $parser->renderer = new CommandLineRenderer($parser);
        $this->addCommonOptionsAndArguments($parser);
        // Sort commands by name.
        $sortedCommands = $pieCrust->getPluginLoader()->getCommands();
        usort($sortedCommands, function ($com1, $com2) {
            return strcmp($com1->getName(), $com2->getName());
        });
        // Add commands to the parser.
        foreach ($sortedCommands as $command) {
            $commandParser = $parser->addCommand($command->getName());
            $command->setupParser($commandParser, $pieCrust);
        }

        // Parse the command line.
        try {
            $result = $parser->parse($userArgc, $userArgv);
        }
        catch (Exception $e) {
            $parser->displayError($e->getMessage(), false);
            return 3;
        }

        // If no command was given, use `help`.
        if (empty($result->command_name)) {
            $result = $parser->parse(2, array('piecrust', 'help'));
        }

        // Create the log.
        $debugMode = $result->options['debug'];
        $quietMode = $result->options['quiet'];
        if ($debugMode && $quietMode)
        {
            $parser->displayError("You can't specify both --debug and --quiet.", false);
            return 1;
        }

        // Run the command.
        foreach ($pieCrust->getPluginLoader()->getCommands() as $command) {
            if ($command->getName() == $result->command_name) {
                try {
                    if ($rootDir == null && $command->requiresWebsite()) {
                        $cwd = getcwd();
                        throw new PieCrustException("No PieCrust website in '{$cwd}' ('_content/config.yml' not found!).");
                    }

                    if ($debugMode) {
                        //$log->debug("PieCrust v." . PieCrustDefaults::VERSION);
                        //$log->debug("  Website: {$rootDir}");
                    }

                    $context = new Context($pieCrust, $result);
                    /*$context->setVerbosity($debugMode ?
                        'debug' :
                        ($quietMode ? 'quiet' : 'default')
                    );*/
                    $command->run($context);
                    return;
                }
                catch (Exception $e) {
                    $pieCrust->getEnvironment()->getLog()->fatal($e->getMessage());
                    return 1;
                }
            }
        }
    }

    protected function getOptions()
    {
        return array(
            array('long' => 'root',
                  'description' => "The root directory of the website (defaults to the first parent of the current directory that contains a '_content' directory).",
                  'type' => CommandInfo::REQUIRED_ARGUMENT),
            array('long' => 'config',
                  'description' => "The configuration variant to use for this command.",
                  'type' => CommandInfo::REQUIRED_ARGUMENT),
            array('long' => 'theme',
                  'description' => "Treat a theme like a website.",
                  'type' => CommandInfo::NO_ARGUMENT),
            array('long' => 'debug',
                  'description' => "Show debug information.",
                  'type' => CommandInfo::NO_ARGUMENT),
            array('long' => 'no-cache',
                  'description' => "When applicable, disable caching.",
                  'type' => CommandInfo::NO_ARGUMENT),
            array('long' => 'quiet',
                  'description' => "Print only important information.",
                  'type' => CommandInfo::NO_ARGUMENT)
        );
    }
}

