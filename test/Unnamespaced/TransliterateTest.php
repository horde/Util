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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Transliterator;
use Horde\Util\Test\Mock\Transliterate;

#[CoversClass(Transliterate::class)]
class TransliterateTest extends TestCase
{
    /**
     * @dataProvider fallbackDataProvider
     */
    #[DataProvider('fallbackDataProvider')]
    public function testTransliterateToAsciiFallback($str, $expected)
    {
        $this->assertEquals(
            $expected,
            Transliterate::testFallback($str)
        );
    }

    public static function fallbackDataProvider()
    {
        return [
            // No normalization
            ['ABC123abc', 'ABC123abc'],
            // Non-ascii can all be transliterated
            ['AÀBÞEÉSß', 'AABTHEESss'],
            // Some non-ascii cannot be transliterated
            ['AÀ黾BÞ', 'AA黾BTH'],
        ];
    }

    /**
     * @dataProvider intlDataProvider
     */
    #[DataProvider('intlDataProvider')]
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

    public static function intlDataProvider()
    {
        return [
            // No normalization
            ['ABC123abc', 'ABC123abc'],
            // Non-ascii can all be transliterated
            ['AÀBÞEÉSß', 'AABTHEESss'],
            // Some non-ascii cannot be transliterated
            ['AÀ黾BÞ', 'AA mianBTH'],
        ];
    }

    /**
     * @dataProvider iconvDataProviderGood
     */
    #[DataProvider('iconvDataProviderGood')]
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
    #[DataProvider('iconvDataProviderBad')]
    public function testTransliterateToAsciiIconvBad($str, $expected)
    {
        if (!extension_loaded('iconv')) {
            $this->markTestSkipped('iconv extension not installed');
        }

        set_error_handler(function () {});
        $result = Transliterate::testIconv($str);
        restore_error_handler();

        // Different iconv versions may fail (false) or succeed with substitution
        $this->assertTrue(
            $result === false || $result === $expected,
            "Expected false or '$expected', got: " . var_export($result, true)
        );
    }

    public static function iconvDataProviderGood()
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
    public static function iconvDataProviderBad()
    {
        return [
            // Some non-ascii cannot be transliterated
            ['AÀ黾B', 'AA?B'],
        ];
    }
}
