<?php

namespace PieCrust\Unit\Tests;

use PieCrust\Command\Command;
use PieCrust\Command\CommandException;
use PieCrust\Mock\MockFileSystem;

use PieCrust\Tests\PieCrustTestCase;


class PieCrustCommandTest extends PieCrustTestCase
{
    public function testGlobalOptions()
    {
        $cmd = new Command();
        $cmd->setup();
        $opt = $this->invokeMethod($cmd,'getOptionObject');
        $this->assertNotNull($opt->getOption('root',true));
        $this->assertNotNull($opt->getOption('theme',true));
        $this->assertNotNull($opt->getOption('config',true));
        $this->assertNotNull($opt->getOption('debug',true));
        $this->assertNotNull($opt->getOption('no-cache',true));
        $this->assertNotNull($opt->getOption('quiet',true));
    }

    public function testUnknownOptionIsNull()
    {
        $cmd = new Command();
        $cmd->setup();
        $opt = $this->invokeMethod($cmd,'getOptionObject');
        $this->assertNull($opt->getOption('unknown-option'));
    }

    public function testDefaultRootdir()
    {
        $cmd = new Command();
        $cmd->setup();

        $rootdir = $this->invokeMethod($cmd,'getRootdir');
        $this->assertNull($rootdir);
    }

    public function testRegisterBakeCommand()
    {
        $cmd = new Command();
        $cmd->registerCommand('PieCrust\\Command\\Bake');
        $this->assertNull(null);
    }

    public function testRegisterUnknownCommand()
    {
        $this->expectException(CommandException::class);
        $cmd = new Command();
        $cmd->registerCommand('PieCrust\\Command\\MaybeATypo');
    }
}

