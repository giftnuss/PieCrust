<?php

namespace PieCrust\Command;

use \Exception;
use GetOpt\GetOpt;
use GetOpt\Option;
use PieCrust\PieCrust;
use PieCrust\PieCrustDefaults;
use PieCrust\PieCrustException;
use PieCrust\Plugins\PluginLoader;
use PieCrust\Util\PathHelper;


/**
 * The piecrust command line application.
 */
class Command
{
    /**
     * The rootdir
     */
    protected $rootdir;

    /**
     * The piecrust object
     */
    protected $piecrust;

    /**
     * The registered commands
     */
    protected $commands;

    /**
     * The command context
     */
    protected $context;

    /**
     * Builds a new instance of piecrust command.
     */
    public function __construct()
    {
        $this->commands = [];
        $this->context = new Context();
        $this->getopt = new GetOpt();
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
    }

    public function registerCommand($namespace)
    {
        $info = $this->fabricateObject($namespace,'Info');
        $name = $info->getName();
        if(empty($this->commands[$name])) {
            $this->commands[$name] = $namespace;

            $cmd = new \GetOpt\Command($name, function () {
                echo "ggggg";
            });
            $this->getopt->addCommand($cmd);
        }
        else {
            throw new CommandException("Command $name is already registered.");
        }
    }

    public function run ($args) {
        $this->getopt->process($args);

        $command = $this->getopt->getCommand();
        if (!$command) {
            // no command given - show help?
        } else {
            $this->prepareRootdir();
            $this->preparePiecrust();
            $this->prepareContext($command);
            // do something with the command - example:
            $handler = $command->getHandler();
            $handler();
        }
    }

    protected function fabricateObject($namespace,$name)
    {
        $class = $namespace . "\\$name";
        if(class_exists($class)) {
            return new $class();
        }
        throw new CommandException("No $name class defined in command namespace $namespace.");
    }

    protected function prepareContext($command)
    {
        $commandname = $command->getName();
        $namespace = $this->commands[$commandname];
        $this->context->setCommandname($commandname);
        $this->context->setNamespace($namespace);

        $info = $this->fabricateObject($namespace,'Info');
        $options = array_merge($this->getOptions(),$info->getOptions());
        foreach($options as $opt) {
            $this->context->setOption($opt['long'], $this->getopt->getOption($opt['long']));
        }
        $this->context->setApp($this->piecrust);
    }

    protected function getOptionObject()
    {
        return $this->getopt;
    }

    protected function getRootdir()
    {
        return $this->rootdir;
    }

    protected function prepareRootdir()
    {
        $rootdir = $this->getopt->getOption('root');
        $themesite = $this->getopt->getOption('theme');
        if($rootdir === null) {
            $rootdir = PathHelper::getAppRootDir(getcwd(), $themesite);
        }
        else {
            if (substr($rootdir, 0, 1) == '~') {
                $rootdir = getenv("HOME") . substr($rootdir, 1);
            }
        }

        // todo $this->validateRootdir($rootdir);
        $this->rootdir = $rootdir;
    }

    protected function preparePiecrust()
    {
        $args = array(
            'root' => $this->getRootdir()
        );

    }

    protected function registerOption($opt) {
        $mapping = [null,
            GetOpt::NO_ARGUMENT,
            GetOpt::REQUIRED_ARGUMENT,
            GetOpt::OPTIONAL_ARGUMENT
        ];
        $option = new Option($opt['short'],$opt['long'],$mapping[$opt['type']]);
        $this->getopt->addOption($option);
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

