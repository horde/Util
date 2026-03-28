<?php

/**
 * Simple test to verify PHPUnit setup
 */

namespace Horde\Util\Test;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use Horde_Util;

#[CoversNothing]
class SimpleTest extends TestCase
{
    public function testTrueIsTrue()
    {
        $this->assertTrue(true);
    }

    public function testAddition()
    {
        $this->assertEquals(4, 2 + 2);
    }

    public function testStringConcatenation()
    {
        $this->assertEquals('Hello World', 'Hello' . ' ' . 'World');
    }

    public function testHordeUtilClassExists()
    {
        $this->assertTrue(class_exists('Horde_Util'));
    }

    public function testCanInstantiateHordeUtil()
    {
        $util = new Horde_Util();
        $this->assertInstanceOf('Horde_Util', $util);
    }
}
