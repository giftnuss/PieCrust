<?php

namespace PieCrust\Command;

use \Exception;
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
     * Builds a new instance of piecrust command.
     */
    public function __construct()
    {
    }

    /**
     * Runs piecrust given some command-line arguments.
     */
    public function run($userArgc = null, $userArgv = null)
    {
        // Get the arguments.
        if ($userArgc == null || $userArgv == null)
        {
            $getopt = new \Console_Getopt();
            $userArgv = $getopt->readPHPArgv();
            // `readPHPArgv` returns a `PEAR_Error` (or something like it) if
            // it can't figure out the CLI arguments.
            if (!is_array($userArgv))
                throw new PieCrustException($userArgv->getMessage());
            $userArgc = count($userArgv);
        }

        // Find if whether the `--root` or `--config` parameters were given.
        $rootDir = null;
        $isThemeSite = false;
        $configVariant = null;
        for ($i = 1; $i < count($userArgv); ++$i)
        {
            $arg = $userArgv[$i];

            if (substr($arg, 0, strlen('--root=')) == '--root=')
            {
                $rootDir = substr($arg, strlen('--root='));
                if (substr($rootDir, 0, 1) == '~')
                    $rootDir = getenv("HOME") . substr($rootDir, 1);
            }
            elseif ($arg == '--root')
            {
                $rootDir = $userArgv[$i + 1];
                ++$i;
            }
            elseif (substr($arg, 0, strlen('--config=')) == '--config=')
            {
                $configVariant = substr($arg, strlen('--config='));
            }
            elseif ($arg == '--config')
            {
                $configVariant = $userArgv[$i + 1];
                ++$i;
            }
            elseif ($arg == '--theme')
            {
                $isThemeSite = true;
            }
            else if ($arg[0] != '-')
            {
                // End of the global arguments sections. This is
                // the command name.
                break;
            }
        }
        if ($rootDir == null)
        {
            // No root given. Find it ourselves.
            $rootDir = PathHelper::getAppRootDir(getcwd(), $isThemeSite);
        }
        else
        {
            // The root was given.
            $rootDir = trim($rootDir, " \"");
            if (!is_dir($rootDir))
                throw new PieCrustException("The given root directory doesn't exist: " . $rootDir);
        }

        // Build the appropriate app.
        if ($rootDir == null)
        {
            $pieCrust = new NullPieCrust();
        }
        else
        {
            $environment = new Environment();
            $pieCrust = new PieCrust(array(
                'root' => $rootDir,
                'cache' => !in_array('--no-cache', $userArgv),
                'environment' => $environment,
                'theme_site' => $isThemeSite
            ));
        }

        // Pre-load the correct config variant if any was specified.
        if ($configVariant != null)
        {
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
        foreach ($sortedCommands as $command)
        {
            $commandParser = $parser->addCommand($command->getName());
            $command->setupParser($commandParser, $pieCrust);
        }

        // Parse the command line.
        try
        {
            $result = $parser->parse($userArgc, $userArgv);
        }
        catch (Exception $e)
        {
            $parser->displayError($e->getMessage(), false);
            return 3;
        }

        // If no command was given, use `help`.
        if (empty($result->command_name))
        {
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
        foreach ($pieCrust->getPluginLoader()->getCommands() as $command)
        {
            if ($command->getName() == $result->command_name)
            {
                try
                {
                    if ($rootDir == null && $command->requiresWebsite())
                    {
                        $cwd = getcwd();
                        throw new PieCrustException("No PieCrust website in '{$cwd}' ('_content/config.yml' not found!).");
                    }

                    if ($debugMode)
                    {
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
                catch (Exception $e)
                {
                    $log->exception($e, $debugMode);
                    return 1;
                }
            }
        }
    }

    protected function addCommonOptionsAndArguments(\Console_CommandLine $parser)
    {
        $parser->addOption('root', array(
            'long_name'   => '--root',
            'description' => "The root directory of the website (defaults to the first parent of the current directory that contains a '_content' directory).",
            'default'     => null,
            'help_name'   => 'ROOT_DIR'
        ));
        $parser->addOption('config_variant', array(
            'long_name'   => '--config',
            'description' => "The configuration variant to use for this command.",
            'default'     => null,
            'help_name'   => 'VARIANT'
        ));
        $parser->addOption('theme_site', array(
            'long_name'   => '--theme',
            'description' => "Treat a theme like a website.",
            'default'     => false,
            'action'      => 'StoreTrue'
        ));
        $parser->addOption('debug', array(
            'long_name'   => '--debug',
            'description' => "Show debug information.",
            'default'     => false,
            'help_name'   => 'DEBUG',
            'action'      => 'StoreTrue'
        ));
        $parser->addOption('no_cache', array(
            'long_name'   => '--no-cache',
            'description' => "When applicable, disable caching.",
            'default'     => false,
            'action'      => 'StoreTrue'
        ));
        $parser->addOption('quiet', array(
            'long_name'   => '--quiet',
            'description' => "Print only important information.",
            'default'     => false,
            'help_name'   => 'QUIET',
            'action'      => 'StoreTrue'
        ));
        $parser->addOption('log', array(
            'long_name'   => '--log',
            'description' => "Send log messages to the specified file.",
            'default'     => null,
            'help_name'   => 'LOG_FILE'
        ));
    }
}

