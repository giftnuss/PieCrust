<?php

namespace PieCrust\Unit\Tests;

use PieCrust\Tests\PieCrustTestCase;

use PieCrust\PieCrust;
use PieCrust\PieCrustException;

use PieCrust\Mock\MockFileSystem;


class PieCrustTest extends PieCrustTestCase
{
    /**
     * @covers PieCrust::__construct
     * @expectedException PieCrust\PieCrustException
     */
    public function testEmptyArgs()
    {
        try {
            $piecrust = new PieCrust();
        }
        catch(PieCrustException $exp) {
            $this->assertEquals("No root directory was specified.",
                $exp->getMessage());
            throw $exp;
        }
    }

    /**
     * @covers PieCrust::__construct
     */
    public function testDefaultPieCrust()
    {
        $fs = MockFileSystem::create();
        $app = new PieCrust(array('root' => $fs->url('kitchen')));

        $this->assertStringMatchesFormat('vfs://root_%i/kitchen/',$app->getRootDir());
        $this->assertTrue($app->isCachingEnabled());
        $this->assertFalse($app->isDebuggingEnabled());
        $this->assertFalse($app->isThemeSite());
    }

    /**
     * @covers PieCrust::addTemplateDir
     * @expectedException PieCrust\PieCrustException
     */
    public function testMissingTemplateDir()
    {
        $fs = MockFileSystem::create();
        $app = new PieCrust(array('root' => $fs->url('kitchen')));

        $app->addTemplatesDir($fs->url('kitchen/xxxxx'));
    }


    /**
     * @covers PieCrust::setPagesDir
     * @expectedException PieCrust\PieCrustException
     */
    public function testMissingPagesDir()
    {
        $fs = MockFileSystem::create();
        $app = new PieCrust(array('root' => $fs->url('kitchen')));

        $app->setPagesDir($fs->url('kitchen/xxxxx'));
    }

    /**
     * @covers PieCrust::setPagesDir
     */
    public function testSetPagesDir()
    {
        $fs = MockFileSystem::create();
        $app = new PieCrust(array('root' => $fs->url('kitchen')));
        $fs->withDir('my_pages');
        $app->setPagesDir($fs->url('my_pages'));

        $this->assertStringMatchesFormat('vfs://root_%i/my_pages/',
            $app->getPagesDir());

    }

    /**
     * @covers PieCrust::setPostsDir
     * @expectedException PieCrust\PieCrustException
     */
    public function testMissingPostsDir()
    {
        $fs = MockFileSystem::create();
        $app = new PieCrust(array('root' => $fs->url('kitchen')));

        $app->setPostsDir($fs->url('kitchen/xxxxx'));
    }
}

