<?php
/**
 * Wrapper to test internal Horde_String methods.
 *
 * @author    Daniel Ziegenberg <daniel@ziegenberg.at>
 * @category  Horde
 * @license   http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package   Util
 */
class Horde_Util_Mock_String extends Horde_String
{
    public static function testConvertCharsetIconv(string $input, string $from, string $to)
    {
        return self::_convertCharsetIconv($input, $from, $to);
    }

    public static function testPosMbstring(string $haystack, string $needle, int $offset, string $charset, string $func)
    {
        return self::_posMbstring($haystack, $needle, $offset, $charset, $func);
    }

    public static function testPosIntl(string $haystack, string $needle, int $offset, string $charset, string $func)
    {
        return self::_posIntl($haystack, $needle, $offset, $charset, $func);
    }
}
