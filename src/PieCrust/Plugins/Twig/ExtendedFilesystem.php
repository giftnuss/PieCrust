<?php

namespace PieCrust\Plugins\Twig;

use
  Twig\Loader\FilesystemLoader,
  Twig\Loader\LoaderInterface,
  Twig\Source;

/**
 * A Twig file system that can also format an in-memory string.
 */
class ExtendedFilesystem extends FilesystemLoader implements LoaderInterface
{
    protected $useTimeInCacheKey;
    protected $templateStrings;

    public function __construct($paths, $useTimeInCacheKey = false)
    {
        parent::__construct($paths);
        $this->useTimeInCacheKey = $useTimeInCacheKey;
        $this->templateStrings = array();
    }

    public function setTemplateSource($name, $source)
    {
        $this->templateStrings[$name] = $source;
    }

    public function getSourceContext($name)
    {
        if (isset($this->templateStrings[$name])) {
            return new Source($this->templateStrings[$name], $name, null);
        }
        return parent::getSourceContext($name);
    }

    public function getCacheKey($name)
    {
        if (isset($this->templateStrings[$name]))
        {
            return $this->templateStrings[$name];
        }

        $cacheKey = parent::getCacheKey($name);
        if ($this->useTimeInCacheKey)
        {
            $path = $this->findTemplate($name);
            $lastModified = filemtime($path);
            $cacheKey .= $lastModified;
        }
        return $cacheKey;
    }

    public function isFresh($name, $time)
    {
        if (isset($this->templateStrings[$name]))
            return false;
        return parent::isFresh($name, $time);
    }
}
