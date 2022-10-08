<?php
/**
 * @author     Jan Schneider <jan@horde.org>
 * @license    http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @category   Horde
 * @package    Util
 * @subpackage UnitTests
 */

namespace Horde\Util\Test;

use PHPUnit\Framework\TestCase;
use Horde\Util\ArrayUtils;

class ArrayUtilsTest extends TestCase
{
    public function setUp(): void
    {
        $this->array = [
            ['name' => 'foo', 'desc' => 'foo long desc'],
            ['name' => 'aaaa', 'desc' => 'aaa foo long desc'],
            ['name' => 'baby', 'desc' => 'The test data was boring'],
            ['name' => 'zebra', 'desc' => 'Striped armadillos'],
            ['name' => 'umbrage', 'desc' => 'resentment'],
        ];
    }

    public function testArraySort()
    {
        ArrayUtils::arraySort($this->array);
        $this->assertEquals(
            [
                1 => ['name' => 'aaaa', 'desc' => 'aaa foo long desc'],
                2 => ['name' => 'baby', 'desc' => 'The test data was boring'],
                0 => ['name' => 'foo', 'desc' => 'foo long desc'],
                4 => ['name' => 'umbrage', 'desc' => 'resentment'],
                3 => ['name' => 'zebra', 'desc' => 'Striped armadillos'],
            ],
            $this->array
        );
    }

    public function testArraySortKey()
    {
        ArrayUtils::arraySort($this->array, 'desc');
        $this->assertEquals(
            [
                1 => ['name' => 'aaaa', 'desc' => 'aaa foo long desc'],
                0 => ['name' => 'foo', 'desc' => 'foo long desc'],
                4 => ['name' => 'umbrage', 'desc' => 'resentment'],
                3 => ['name' => 'zebra', 'desc' => 'Striped armadillos'],
                2 => ['name' => 'baby', 'desc' => 'The test data was boring'],
            ],
            $this->array
        );
    }
}
