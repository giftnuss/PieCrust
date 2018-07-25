<?php

namespace PieCrust\Unit\Tests;

use PieCrust\Tests\PieCrustTestCase;

use PieCrust\PieCrust;
use PieCrust\Baker\PieCrustBaker;
use PieCrust\Mock\MockFileSystem;
use PieCrust\Mock\MockPage;


class PieCrustBakerTest extends PieCrustTestCase
{
    public function testDefaults()
    {
        $fs = MockFileSystem::create();
        $app = $fs->getApp();
        $baker = new PieCrustBaker($app);
        $this->assertStringMatchesFormat(
            'vfs://root_%i/kitchen/_counter/',
            $baker->getBakeDir());
    }

    public function testDefaultPageBaker()
    {
        $fs = MockFileSystem::create();
        $app = $fs->getApp();
        $baker = new PieCrustBaker($app);
        $page = new MockPage($app);

        $pagebaker = $baker->getPageBaker();
        $this->assertEquals($pagebaker->getBakedFiles(),array());
        $this->assertStringMatchesFormat(
            'vfs://root_%i/kitchen/_counter/index.html',
            $pagebaker->getOutputPath($page));
        $this->assertEquals($pagebaker->getPageCount(),0);
    }
}
