<?php
/**
 * @author     Jan Schneider <jan@horde.org>
 * @license    http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @category   Horde
 * @package    Util
 * @subpackage UnitTests
 */

namespace Horde\Util\Test\Unnamespaced;

use PHPUnit\Framework\TestCase;
use Horde_Variables;

class VariablesTest extends TestCase
{
    public function testRemove()
    {
        $vars = new Horde_Variables([
           'a' => 'a',
           'b' => 'b',
           'c' => [1, 2, 3],
           'd' => [
               'z' => 'z',
               'y' => [
                   'f' => 'f',
                   'g' => 'g'
               ]
           ]
        ]);

        $vars->remove('a');
        $vars->remove('d[y][g]');

        $this->assertNull($vars->a);
        $this->assertEquals('b', $vars->b);
        $this->assertEquals([1, 2, 3], $vars->c);
        $this->assertEquals(
            ['z' => 'z',
                  'y' => ['f' => 'f']],
            $vars->d
        );
    }
}
