<?php

namespace PieCrust\Tests;

use Symfony\Component\Yaml\Yaml;
use PieCrust\IPieCrust;
use PieCrust\Page\Page;
use PieCrust\Page\PageConfiguration;
use PieCrust\Page\Filtering\PaginationFilter;
use PieCrust\Util\PathHelper;
use PieCrust\Mock\MockFileSystem;
use PieCrust\Mock\MockPage;
use PieCrust\Mock\MockPieCrust;


class FormatterMarkdownTest extends PieCrustTestCase
{
    public function simpleTextsDataProvider()
    {
        $data = array();
        $data[] = ["*This* is a **sidebar**!",
                   "<p><em>This</em> is a <strong>sidebar</strong>!</p>"];
        $data[] = ["# Introduction\n\n## What is Markdown?",
                   "<h1>Introduction</h1>\n<h2>What is Markdown?</h2>"];

        return $data;
    }

    /**
     * @dataProvider simpleTextsDataProvider
     */
    public function testSimpleTests($text, $expect)
    {
        $fs = MockFileSystem::create()
            ->withPage(
                'foo.html',
                array(),
                "$text"
            );
        $app = $fs->getApp();

        $page = Page::createFromUri($app, 'foo', false);
        $this->assertEquals($expect, trim($page->getContentSegment()));
    }

}
