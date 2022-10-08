<?php
/**
 * @author     Michael Slusarz <slusarz@horde.org>
 * @category   Horde
 * @license    http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package    Util
 * @subpackage UnitTests
 */

namespace Horde\Util\Test\Unnamespaced;

use PHPUnit\Framework\TestCase;
use Transliterator;
use Horde\Util\Test\Mock\Transliterate;

class TransliterateTest extends TestCase
{
    /**
     * @dataProvider fallbackDataProvider
     */
    public function testTransliterateToAsciiFallback($str, $expected)
    {
        $this->assertEquals(
            $expected,
            Transliterate::testFallback($str)
        );
    }

    public function fallbackDataProvider()
    {
        return [
            // No normalization
            ['ABC123abc', 'ABC123abc'],
            // Non-ascii can all be transliterated
            ['AÀBÞEÉSß', 'AABTHEESss'],
            // Some non-ascii cannot be transliterated
            ['AÀ黾BÞ', 'AA黾BTH']
        ];
    }

    /**
     * @dataProvider intlDataProvider
     */
    public function testTransliterateToAsciiIntl($str, $expected)
    {
        if (!class_exists('Transliterator')) {
            $this->markTestSkipped('intl extension not installed or too old');
        }

        $this->assertEquals(
            $expected,
            Transliterate::testIntl($str)
        );
    }

    public function intlDataProvider()
    {
        return [
            // No normalization
            ['ABC123abc', 'ABC123abc'],
            // Non-ascii can all be transliterated
            ['AÀBÞEÉSß', 'AABTHEESss'],
            // Some non-ascii cannot be transliterated
            ['AÀ黾BÞ', 'AA mianBTH']
        ];
    }

    /**
     * @dataProvider iconvDataProviderGood
     */
    public function testTransliterateToAsciiIconvGood($str, $expected)
    {
        if (!extension_loaded('iconv')) {
            $this->markTestSkipped('iconv extension not installed');
        }

        $this->assertEquals(
            $expected,
            Transliterate::testIconv($str)
        );
    }
    /**
     * @dataProvider iconvDataProviderBad
     */
    public function testTransliterateToAsciiIconvBad($str, $expected)
    {
        if (!extension_loaded('iconv')) {
            $this->markTestSkipped('iconv extension not installed');
        }

        $this->expectNotice();
        $this->assertFalse(
            Transliterate::testIconv($str),
            "Cannot convert to: " . $expected
        );
    }

    public function iconvDataProviderGood()
    {
        return [
            // No normalization
            ['ABC123abc', 'ABC123abc'],
            // Non-ascii can all be transliterated
            // Note: We removed the 'Þ' character from the test explicitly,
            // since different versions of glibc transliterate it differently.
            // See https://github.com/horde/horde/pull/144
            ['AÀBEÉSß', 'AABEESss'],
        ];
    }
    public function iconvDataProviderBad()
    {
        return [
            // Some non-ascii cannot be transliterated
            ['AÀ黾B', 'AA?B']
        ];
    }
}
