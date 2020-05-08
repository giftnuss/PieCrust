<?php

namespace PieCrust\Baker;

use PieCrust\IPage;
use PieCrust\IPieCrust;
use PieCrust\Log\Logger;


/**
 * The interface for plugins that want to extra-processing during
 * a site bake.
 */
interface IBakerAssistant
{
    /**
     * Gets the name of the assistant.
     */
    public function getName();

    /**
     * Initializes the assistant.
     */
    public function initialize(IPieCrust $pieCrust, Logger $logger);

    /**
     * Gets called before baking the site.
     */
    public function onBakeStart(IBaker $baker);

    /**
     * Gets called before a page is baked.
     */
    public function onPageBakeStart(IPage $page);

    /**
     * Gets called after a page is baked.
     */
    public function onPageBakeEnd(IPage $page, BakeResult $result);

    /**
     * Gets called after baking the site.
     */
    public function onBakeEnd(IBaker $baker);
}

