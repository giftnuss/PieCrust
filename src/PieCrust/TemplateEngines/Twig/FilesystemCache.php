<?php

namespace Siox;

class Tempfile
{
    protected $persist;

    protected $dir;

    protected $name;

    protected $prefix;

    public static function genName($length = 8)
    {
        return substr(base_convert(mt_rand(),10,36),0,$length);
    }

    public function __construct($dir = null, $prefix = '')
    {
        $this->persist();
        $this->setDir($dir);
        $this->prefix = $prefix;
        $this->name = $this->generateName();
    }

    public function setDir($dir)
    {
        $this->dir = rtrim($dir,'/\\');
    }

    public function getFilename()
    {
        if($this->dir !== null) {
            return join(DIRECTORY_SEPARATOR, [$this->dir, $this->name]);
        }
        return $this->name;
    }

    public function generateName()
    {
        if(strlen($this->prefix) > 0) {
            return join('.',[$this->prefix,self::genName()]);
        }
        return self::genName() . ".tmp";
    }

    public function persist()
    {
        $this->persist = true;
    }

    public function noPersist()
    {
        $this->persist = false;
    }
}

namespace PieCrust\TemplateEngines\Twig;

use \Exception;
use \RuntimeException;
use Twig\Cache\FilesystemCache as Base;
use Siox\Tempfile;

class FilesystemCache extends Base
{
    public function write($key, $content)
    {
        $dir = dirname($key);
        if (!is_dir($dir)) {
            if (false === @mkdir($dir, 0777, true)) {
                if (PHP_VERSION_ID >= 50300) {
                    clearstatcache(true, $dir);
                }
                if (!is_dir($dir)) {
                    throw new RuntimeException(sprintf('Unable to create the cache directory (%s).', $dir));
                }
            }
        } elseif (!is_writable($dir)) {
            throw new RuntimeException(sprintf('Unable to write in the cache directory (%s).', $dir));
        }

        $tmpFile = new Tempfile($dir,basename($key));

        if(false === @file_put_contents($tmpFile->getFilename(), $content) ) {
            throw new RuntimeException(sprintf('Failed to create temporary cache file "%s".', $tmpFile->getFilename()));
        }

        if(false === @rename($tmpFile->getFilename(), $key)) {
            throw new RuntimeException(sprintf('Failed to write cache file "%s".', $key));
        }

        @chmod($key, 0666 & ~umask());
/*
        if (self::FORCE_BYTECODE_INVALIDATION == ($this->options & self::FORCE_BYTECODE_INVALIDATION)) {
            // Compile cached file into bytecode cache
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($key, true);
            } elseif (function_exists('apc_compile_file')) {
                apc_compile_file($key);
            }
        }
        */
    }
}
