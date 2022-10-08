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
use Horde\Util\Util;

class UtilTest extends TestCase
{
    public function testGetPathInfo()
    {
        $_SERVER['SERVER_SOFTWARE'] = '';
        $_SERVER['PATH_INFO'] = '';
        $this->assertEquals('', Util::getPathInfo());

        $_SERVER['PATH_INFO'] = '/foo/bar';
        $this->assertEquals('/foo/bar', Util::getPathInfo());

        $_SERVER['SERVER_SOFTWARE'] = 'lighttpd/1.4.26';
        $_SERVER['PATH_INFO'] = '';
        $_SERVER['REQUEST_URI'] = '/horde/path.php';
        $_SERVER['SCRIPT_NAME'] = '/horde/path.php';
        $this->assertEquals('', Util::getPathInfo());
        $_SERVER['REQUEST_URI'] = '/horde/path.php?baz';
        $_SERVER['QUERY_STRING'] = 'baz';
        $this->assertEquals('', Util::getPathInfo());

        $_SERVER['REQUEST_URI'] = '/horde/path.php/foo/bar';
        $_SERVER['SCRIPT_NAME'] = '/horde/path.php';
        $_SERVER['QUERY_STRING'] = '';
        $this->assertEquals('/foo/bar', Util::getPathInfo());
        $_SERVER['REQUEST_URI'] = '/horde/path.php/foo/bar?baz';
        $_SERVER['QUERY_STRING'] = 'baz';
        $this->assertEquals('/foo/bar', Util::getPathInfo());
        $_SERVER['REQUEST_URI'] = '/horde/foo/bar?baz';
        $this->assertEquals('/foo/bar', Util::getPathInfo());

        $_SERVER['REQUEST_URI'] = '/horde/';
        $_SERVER['SCRIPT_NAME'] = '/horde/index.php';
        $this->assertEquals('', Util::getPathInfo());

        $_SERVER['REQUEST_URI'] = '/horde/index.php';
        $_SERVER['SCRIPT_NAME'] = '/horde/index.php';
        $_SERVER['QUERY_STRING'] = '';
        $this->assertEquals('', Util::getPathInfo());
        $_SERVER['REQUEST_URI'] = '/horde/index.php?baz';
        $_SERVER['QUERY_STRING'] = 'baz';
        $this->assertEquals('', Util::getPathInfo());

        $_SERVER['REQUEST_URI'] = '/horde/index.php/foo/bar';
        $_SERVER['SCRIPT_NAME'] = '/horde/index.php';
        $_SERVER['QUERY_STRING'] = '';
        $this->assertEquals('/foo/bar', Util::getPathInfo());
        $_SERVER['REQUEST_URI'] = '/horde/index.php/foo/bar?baz';
        $_SERVER['QUERY_STRING'] = 'baz';
        $this->assertEquals('/foo/bar', Util::getPathInfo());

        $_SERVER['REQUEST_URI'] = '/test/42?id=42';
        $_SERVER['SCRIPT_NAME'] = '/test/index.php';
        $_SERVER['QUERY_STRING'] = 'id=42&id=42';
        $this->assertEquals('/42', Util::getPathInfo());
    }
}
