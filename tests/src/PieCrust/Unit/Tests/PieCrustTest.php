<?php

namespace PieCrust\Unit\Tests;

use PieCrust\Tests\PieCrustTestCase;

use PieCrust\PieCrust;
use PieCrust\PieCrustException;


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
}

