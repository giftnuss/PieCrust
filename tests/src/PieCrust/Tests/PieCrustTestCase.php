<?php

namespace PieCrust\Tests;

use PHPUnit\Framework\TestCase;
use PieCrust\Util\PathHelper;


class PieCrustTestCase extends TestCase
{
    public static function tearDownAfterClass(): void
    {
        $mockDir = PIECRUST_UNITTESTS_MOCK_DIR;
        if (is_dir($mockDir))
        {
            // On Windows, it looks like the file-system is a bit "slow".
            // And by "slow", I mean "retarded".
            $tries = 3;
            while ($tries > 0)
            {
                try
                {
                    PathHelper::deleteDirectoryContents($mockDir);
                    rmdir($mockDir);
                    $tries = 0;
                }
                catch (\Exception $e)
                {
                    $tries--;
                }
            }
        }
    }

    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }
}

