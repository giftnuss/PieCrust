<?php

namespace PieCrust\Tests;

use PieCrust\PieCrust;
use PieCrust\Page\Assetor;
use PieCrust\Page\Page;
use PieCrust\Mock\MockFileSystem;


class AssetorTest extends PieCrustTestCase
{
    public function assetorDataProvider(): array
    {
        return array(
            'one-asset' => array(
                function () {
                    return MockFileSystem::create()
                        ->withPage('foo/bar')
                        ->withPageAsset('foo/bar', 'one.txt', 'one');
                },
                array('one' => 'one')
            ),
            'multiple-assets' => array(
                function () {
                    return MockFileSystem::create()
                        ->withPage('foo/bar')
                        ->withPageAsset('foo/bar', 'one.txt', 'one')
                        ->withPageAsset('foo/bar', 'two.txt', 'two');
                },
                array('one' => 'one', 'two' => 'two')
            )
        );
    }

    /**
     * @dataProvider assetorDataProvider
     */
    public function testAssetor($fsCreator, array $expectedAssets): void
    {
        $fs = $fsCreator();
        $pc = $fs->getApp();
        $page = Page::createFromUri($pc, 'foo/bar', false);
        $assetor = new Assetor($page);

        foreach ($expectedAssets as $name => $contents) {
            $this->assertTrue(isset($assetor[$name]));
            $this->assertEquals(
                '/_content/pages/foo/bar-assets/' . $name . '.txt',
                $assetor[$name]);
        }
    }

    public function testAssetorWithoutAsset(): void
    {
        $fs = MockFileSystem::create()->withPage('foo/bar');

        $pc = $fs->getApp();
        $page = Page::createFromUri($pc, 'foo/bar', false);
        $assetor = new Assetor($page);

        $this->assertFalse($assetor->getUrlBase());
    }

    public function testMissingAsset()
    {
        $this->expectException(\PieCrust\PieCrustException::class);

        $fs = MockFileSystem::create()->withPage('foo/bar');
        $pc = new PieCrust(array('root' => $fs->getAppRoot()));
        $page = Page::createFromUri($pc, 'foo/bar', false);
        $assetor = new Assetor($page);
        $tmp = isset($assetor['blah']);
    }

    public function testSeveralAssetsWithSameFilename()
    {
        $this->expectException(\PieCrust\PieCrustException::class);

        $fs = MockFileSystem::create()
            ->withPage('foo/bar')
            ->withPageAsset('foo/bar', 'one.txt', 'one')
            ->withPageAsset('foo/bar', 'one.xml', 'another one');
        $pc = new PieCrust(array('root' => $fs->getAppRoot()));
        $page = Page::createFromUri($pc, 'foo/bar', false);
        $assetor = new Assetor($page);
        $tmp = $assetor['one'];
    }
}

