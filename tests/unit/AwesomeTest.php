<?php
/**
 * Created by solly [25.05.17 1:59]
 */

namespace tests\unit;

use Codeception\Test\Unit;

class AwesomeTest extends Unit
{
    public function testDummy()
    {
        $var = 2 + 3;
        verify($var)->equals(5);
    }
}
