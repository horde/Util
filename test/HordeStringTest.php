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
use Horde\Util\HordeString;

class HordeStringTest extends TestCase
{
    public function tearDown(): void
    {
        setlocale(LC_ALL, '');
    }

    public function testUpper()
    {
        $this->assertEquals(
            'ABCDEFGHII',
            HordeString::upper('abCDefGHiI', true, 'us-ascii')
        );
        $this->assertEquals(
            'ABCDEFGHII',
            HordeString::upper('abCDefGHiI', true, 'Big5')
        );
        $this->assertEquals(
            'ABCDEFGHİI',
            HordeString::convertCharset(
                HordeString::upper('abCDefGHiI', true, 'iso-8859-9'),
                'iso-8859-9',
                'utf-8'
            )
        );
    }

    public function testUpperTurkish()
    {
        if (!setlocale(LC_ALL, 'tr_TR')) {
            $this->markTestSkipped('No Turkish locale installed.');
        }
        $one = HordeString::convertCharset(
            strtoupper('abCDefGHiI'),
            'iso-8859-9',
            'utf-8'
        );
        $two = HordeString::upper('abCDefGHiI');
        setlocale(LC_ALL, 'C');
        $this->assertEquals('ABCDEFGHİI', $one);
        $this->assertEquals('ABCDEFGHII', $two);
    }

    public function testLower()
    {
        $this->assertEquals(
            'abcdefghii',
            HordeString::lower('abCDefGHiI', true, 'us-ascii')
        );
        $this->assertEquals(
            'abcdefghii',
            HordeString::lower('abCDefGHiI', true, 'Big5')
        );
        $this->assertEquals(
            'abcdefghiı',
            HordeString::convertCharset(
                HordeString::lower('abCDefGHiI', true, 'iso-8859-9'),
                'iso-8859-9',
                'utf-8'
            )
        );
    }

    public function testLowerTurkish()
    {
        if (!setlocale(LC_ALL, 'tr_TR')) {
            $this->markTestSkipped('No Turkish locale installed.');
        }
        $one = HordeString::convertCharset(
            strtolower('abCDefGHiI'),
            'iso-8859-9',
            'utf-8'
        );
        $two = HordeString::lower('abCDefGHiI');
        setlocale(LC_ALL, 'C');
        $this->assertEquals('abcdefghiı', $one);
        $this->assertEquals('abcdefghii', $two);
    }

    public function testUcfirst()
    {
        $this->assertEquals(
            'Integer',
            HordeString::ucfirst('integer', true, 'us-ascii')
        );
        $this->assertEquals(
            'Integer',
            HordeString::ucfirst('integer', true, 'Big5')
        );
        $this->assertEquals(
            'İnteger',
            HordeString::convertCharset(
                HordeString::ucfirst('integer', true, 'iso-8859-9'),
                'iso-8859-9',
                'utf-8'
            )
        );
    }

    public function testUcwords()
    {
        $this->assertEquals(
            'Integer  Inside',
            HordeString::ucwords('integer  inside', true, 'us-ascii')
        );
        $this->assertEquals(
            'Integer  Inside',
            HordeString::ucwords('integer  inside', true, 'Big5')
        );
        $this->assertEquals(
            'İnteger  İnside',
            HordeString::convertCharset(
                HordeString::ucwords('integer  inside', true, 'iso-8859-9'),
                'iso-8859-9',
                'utf-8'
            )
        );
    }

    public function testUcfirstTurkish()
    {
        if (!setlocale(LC_ALL, 'tr_TR')) {
            $this->markTestSkipped('No Turkish locale installed.');
        }
        $one = HordeString::convertCharset(
            ucfirst('integer'),
            'iso-8859-9',
            'utf-8'
        );
        $two = HordeString::ucfirst('integer');
        setlocale(LC_ALL, 'C');
        $this->assertEquals('İnteger', $one);
        $this->assertEquals('Integer', $two);
    }

    public function testLength()
    {
        $this->assertEquals(7, HordeString::length('Welcome', 'Big5'));
        $this->assertEquals(7, HordeString::length('Welcome', 'Big5'));
        $this->assertEquals(
            2,
            HordeString::length(
                HordeString::convertCharset('歡迎', 'utf-8', 'Big5'),
                'Big5'
            )
        );
        $this->assertEquals(2, HordeString::length('歡迎', 'utf-8'));

        /* The following strings were taken with permission from the UTF-8
         * sampler by Frank da Cruz <fdc@columbia.edu> and the Kermit Project
         * (http://www.columbia.edu/kermit/).  The original page is located at
         * http://www.columbia.edu/kermit/utf8.html */

        // French 50
        $this->assertEquals(
            50,
            HordeString::length('Je peux manger du verre, ça ne me fait pas de mal.', 'utf-8')
        );

        // Spanish 36
        $this->assertEquals(
            36,
            HordeString::length('Puedo comer vidrio, no me hace daño.', 'utf-8')
        );

        // Portuguese 34
        $this->assertEquals(
            34,
            HordeString::length('Posso comer vidro, não me faz mal.', 'utf-8')
        );

        // Brazilian Portuguese 34
        $this->assertEquals(
            34,
            HordeString::length('Posso comer vidro, não me machuca.', 'utf-8')
        );

        // Italian 41
        $this->assertEquals(
            41,
            HordeString::length('Posso mangiare il vetro e non mi fa male.', 'utf-8')
        );

        // English 39
        $this->assertEquals(
            39,
            HordeString::length('I can eat glass and it doesn\'t hurt me.', 'utf-8')
        );

        // Norsk/Norwegian/Nynorsk 33
        $this->assertEquals(
            33,
            HordeString::length('Eg kan eta glas utan å skada meg.', 'utf-8')
        );

        // Svensk/Swedish 36
        $this->assertEquals(
            36,
            HordeString::length('Jag kan äta glas utan att skada mig.', 'utf-8')
        );

        // Dansk/Danish 45
        $this->assertEquals(
            45,
            HordeString::length('Jeg kan spise glas, det gør ikke ondt på mig.', 'utf-8')
        );

        // Deutsch/German 41
        $this->assertEquals(
            41,
            HordeString::length('Ich kann Glas essen, ohne mir weh zu tun.', 'utf-8')
        );

        // Russian 38
        $this->assertEquals(
            38,
            HordeString::length('Я могу есть стекло, оно мне не вредит.', 'utf-8')
        );
    }

    /**
     * @dataProvider posProvider
     */
    public function testPos($str, $search, $pos)
    {
        $this->assertEquals(
            $pos,
            HordeString::pos($str, $search)
        );
    }

    public function posProvider()
    {
        return [
            ['Schöne Neue Welt', 'ö', 3],
            ['Schöne Neue Welt', 'N', 7],
            ['Schöne Neue Welt', 'e', 5],
            ['Schöne Neue Welt', ' ', 6],
            ['Schöne Neue Welt', 'a', false]
        ];
    }

    /**
     * @dataProvider iposProvider
     */
    public function testIpos($str, $search, $pos)
    {
        $this->assertEquals(
            $pos,
            HordeString::ipos($str, $search)
        );
    }

    public function iposProvider()
    {
        return [
            ['Schöne Neue Welt', 'Ö', 3],
            ['Schöne Neue Welt', 'N', 4],
            ['Schöne Neue Welt', 'E', 5],
            ['Schöne Neue Welt', ' ', 6],
            ['Schöne Neue Welt', 'a', false]
        ];
    }

    /**
     * @dataProvider rposProvider
     */
    public function testRpos($str, $search, $pos)
    {
        $this->assertEquals(
            $pos,
            HordeString::rpos($str, $search)
        );
    }

    public function rposProvider()
    {
        return [
            ['Schöne Neue Welt', 'ö', 3],
            ['Schöne Neue Welt', 'N', 7],
            ['Schöne Neue Welt', 'e', 13],
            ['Schöne Neue Welt', ' ', 11],
            ['Schöne Neue Welt', 'a', false]
        ];
    }

    /**
     * @dataProvider riposProvider
     */
    public function testRipos($str, $search, $pos)
    {
        $this->assertEquals(
            $pos,
            HordeString::ripos($str, $search)
        );
    }

    public function riposProvider()
    {
        return [
            ['Schöne Neue Welt', 'Ö', 3],
            ['Schöne Neue Welt', 'N', 7],
            ['Schöne Neue Welt', 'E', 13],
            ['Schöne Neue Welt', ' ', 11],
            ['Schöne Neue Welt', 'a', false]
        ];
    }

    public function testPad()
    {
        /* Simple single byte tests. */
        $this->assertEquals(
            'abc',
            HordeString::pad('abc', 2)
        );
        $this->assertEquals(
            'abc',
            HordeString::pad('abc', 3)
        );
        $this->assertEquals(
            'abc ',
            HordeString::pad('abc', 4)
        );
        $this->assertEquals(
            ' abc',
            HordeString::pad('abc', 4, ' ', STR_PAD_LEFT)
        );
        $this->assertEquals(
            'abc ',
            HordeString::pad('abc', 4, ' ', STR_PAD_RIGHT)
        );
        $this->assertEquals(
            'abc ',
            HordeString::pad('abc', 4, ' ', STR_PAD_BOTH)
        );
        $this->assertEquals(
            '  abc',
            HordeString::pad('abc', 5, ' ', STR_PAD_LEFT)
        );
        $this->assertEquals(
            'abc  ',
            HordeString::pad('abc', 5, ' ', STR_PAD_RIGHT)
        );
        $this->assertEquals(
            ' abc ',
            HordeString::pad('abc', 5, ' ', STR_PAD_BOTH)
        );

        /* Long padding tests. */
        $this->assertEquals(
            '=-+=-+=abc',
            HordeString::pad('abc', 10, '=-+', STR_PAD_LEFT)
        );
        $this->assertEquals(
            'abc=-+=-+=',
            HordeString::pad('abc', 10, '=-+', STR_PAD_RIGHT)
        );
        $this->assertEquals(
            '=-+abc=-+=',
            HordeString::pad('abc', 10, '=-+', STR_PAD_BOTH)
        );

        /* Multibyte tests. */
        $this->assertEquals(
            ' äöü',
            HordeString::pad('äöü', 4, ' ', STR_PAD_LEFT, 'utf-8')
        );
        $this->assertEquals(
            'äöü ',
            HordeString::pad('äöü', 4, ' ', STR_PAD_RIGHT, 'utf-8')
        );
        $this->assertEquals(
            'äöü ',
            HordeString::pad('äöü', 4, ' ', STR_PAD_BOTH, 'utf-8')
        );
        $this->assertEquals(
            'äöüäöüäabc',
            HordeString::pad('abc', 10, 'äöü', STR_PAD_LEFT, 'utf-8')
        );
        $this->assertEquals(
            'abcäöüäöüä',
            HordeString::pad('abc', 10, 'äöü', STR_PAD_RIGHT, 'utf-8')
        );
        $this->assertEquals(
            'äöüabcäöüä',
            HordeString::pad('abc', 10, 'äöü', STR_PAD_BOTH, 'utf-8')
        );

        /* Special cases. */
        $this->assertEquals(
            'abc ',
            HordeString::pad('abc', 4, ' ', STR_PAD_RIGHT, 'utf-8')
        );
    }

    /**
     * @dataProvider substrProvider
     */
    public function testSubstr($match, $string, $start, $length)
    {
        $this->assertEquals(
            $match,
            HordeString::substr($string, $start, $length, 'utf-8')
        );
    }

    public function substrProvider()
    {
        return [
            [
                't ämet',
                "Lörem ipsüm dölör sit ämet",
                20,
                null
            ],
            [
                't ämet',
                "Lörem ipsüm dölör sit ämet",
                -6,
                null
            ],
            [
                'Lörem',
                "Lörem ipsüm dölör sit ämet",
                0,
                5
            ],
            [
                'Lörem',
                "Lörem ipsüm dölör sit ämet",
                0,
                -21
            ],
            [
                'ipsüm',
                "Lörem ipsüm dölör sit ämet",
                6,
                5
            ],
            /* These are illegal UTF-8 encodings. */
            [
                '',
                base64_decode('2KvYpw=='),
                2,
                2
            ],
            [
                '',
                base64_decode('2KU='),
                1,
                1
            ],
            [
                '',
                base64_decode('2KvYpw=='),
                2,
                2
            ],
            [
                '',
                base64_decode('2KI='),
                1,
                1
            ],
            [
                '',
                base64_decode('5L6L'),
                1,
                1
            ],
            [
                '',
                base64_decode('5rWL'),
                1,
                1
            ],
            [
                '',
                base64_decode('5ris'),
                1,
                1
            ],
            [
                '',
                base64_decode('0L/RgNC40LzQtQ=='),
                5,
                5
            ],
            [
                '',
                base64_decode('0LA='),
                1,
                1
            ],
            [
                '',
                base64_decode('4KSJ'),
                1,
                1
            ],
            [
                '',
                base64_decode('4KSq4KSw4KSV'),
                3,
                3
            ],
        ];
    }

    public function testWordwrap()
    {
        // Test default parameters and break character.
        $string = "Lörem ipsüm dölör sit ämet, cönsectetüer ädipiscing elit. Aliqüäm söllicitüdin fäücibüs mäüris ämet.";
        $this->assertEquals(
            <<<EOT
Lörem ipsüm dölör sit ämet, cönsectetüer ädipiscing elit. Aliqüäm
söllicitüdin fäücibüs mäüris ämet.
EOT
            ,
            HordeString::wordwrap($string)
        );
        $this->assertEquals(
            <<<EOT
Lörem ipsüm dölör sit ämet,
  cönsectetüer ädipiscing elit.
  Aliqüäm söllicitüdin fäücibüs
  mäüris ämet.
EOT
            ,
            HordeString::wordwrap($string, 31, "\n  ")
        );
        $this->assertEquals(
            <<<EOT
Lörem ipsüm dölör sit ämet,
  cönsectetüer ädipiscing
  elit. Aliqüäm söllicitüdin
  fäücibüs mäüris ämet.
EOT
            ,
            HordeString::wordwrap($string, 29, "\n  ")
        );

        // Test existing line breaks.
        $string = "Lörem ipsüm dölör sit ämet, cönsectetüer ädipiscing elit.\nAliqüäm söllicitüdin fäücibüs mäüris ämet.";
        $this->assertEquals(
            <<<EOT
Lörem ipsüm dölör sit ämet, cönsectetüer ädipiscing elit.
Aliqüäm söllicitüdin fäücibüs mäüris ämet.
EOT
            ,
            HordeString::wordwrap($string)
        );
        $string = "Lörem ipsüm dölör sit ämet, cönsectetüer ädipiscing elit. Aliqüäm\nsöllicitüdin fäücibüs mäüris ämet.";
        $this->assertEquals(
            <<<EOT
Lörem ipsüm dölör sit ämet, cönsectetüer ädipiscing elit. Aliqüäm
söllicitüdin fäücibüs mäüris ämet.
EOT
            ,
            HordeString::wordwrap($string)
        );
        $string = "Lörem ipsüm dölör sit ämet, cönsectetüer ädipiscing elit. Aliqüäm söllicitüdin\nfäücibüs mäüris ämet.";
        $this->assertEquals(
            <<<EOT
Lörem ipsüm dölör sit ämet, cönsectetüer ädipiscing elit. Aliqüäm
söllicitüdin
fäücibüs mäüris ämet.
EOT
            ,
            HordeString::wordwrap($string)
        );
        $string = "Lörem ipsüm dölör sit ämet, cönsectetüer ädipiscing elit. Aliqüäm söllicitüdin fäücibüs mäüris ämet.\nLörem ipsüm dölör sit ämet.\nLörem ipsüm dölör sit ämet, cönsectetüer ädipiscing elit. Aliqüäm söllicitüdin fäücibüs mäüris ämet.";
        $this->assertEquals(
            <<<EOT
Lörem ipsüm dölör sit ämet, cönsectetüer ädipiscing elit. Aliqüäm
söllicitüdin fäücibüs mäüris ämet.
Lörem ipsüm dölör sit ämet.
Lörem ipsüm dölör sit ämet, cönsectetüer ädipiscing elit. Aliqüäm
söllicitüdin fäücibüs mäüris ämet.
EOT
            ,
            HordeString::wordwrap($string)
        );

        // Test overlong words and word cut.
        $string = "Löremipsümdölörsitämet, cönsectetüerädipiscingelit.";
        $this->assertEquals(
            <<<EOT
Löremipsümdölörsitämet,
cönsectetüerädipiscingelit.
EOT
            ,
            HordeString::wordwrap($string, 15)
        );
        $string = "Löremipsümdölörsitämet, cönsectetüerädipiscingelit.";
        $this->assertEquals(
            <<<EOT
Löremipsümdölör
sitämet,
cönsectetüerädi
piscingelit.
EOT
            ,
            HordeString::wordwrap($string, 15, "\n", true)
        );

        // Test whitespace at wrap width.
        $string = "Lörem ipsüm dölör sit ämet, cönsectetüer ädipiscing";
        $this->assertEquals(
            <<<EOT
Lörem ipsüm dölör sit ämet,
cönsectetüer ädipiscing
EOT
            ,
            HordeString::wordwrap($string, 27)
        );
        $this->assertEquals(
            <<<EOT
Lörem ipsüm dölör sit ämet,
cönsectetüer ädipiscing
EOT
            ,
            HordeString::wordwrap($string, 28)
        );

        // Test line folding.
        $string = "Löremipsümdölörsitämet, cönsectetüerädipiscingelit.";
        $this->assertEquals(
            <<<EOT
Löremipsümdölör
sitämet,
 cönsectetüeräd
ipiscingelit.
EOT
            ,
            HordeString::wordwrap($string, 15, "\n", true, true)
        );
        $string = "Lörem ipsüm dölör sit ämet,  cönsectetüer ädipiscing elit.  Aliqüäm söllicitüdin fäücibüs mäüris ämet.";
        $this->assertEquals(
            <<<EOT
Lörem ipsüm dölör sit ämet,
  cönsectetüer ädipiscing elit.
  Aliqüäm söllicitüdin fäücibüs
 mäüris ämet.
EOT
            ,
            HordeString::wordwrap($string, 31, "\n", false, true)
        );
        $string = "Lörem ipsüm dölör sit; ämet:  cönsectetüer ädipiscing elit.  Aliqüäm söllicitüdin fäücibüs mäüris ämet.";
        $this->assertEquals(
            <<<EOT
Lörem ipsüm dölör sit;
 ämet:
  cönsectetüer ädipiscing elit.
  Aliqüäm söllicitüdin fäücibüs
 mäüris ämet.
EOT
            ,
            HordeString::wordwrap($string, 31, "\n", false, true)
        );
        $string = "Lörem ipsüm dölör sit; ämet:cönsectetüer ädipiscing elit.  Aliqüäm söllicitüdin fäücibüs mäüris ämet.";
        $this->assertEquals(
            <<<EOT
Lörem ipsüm dölör sit;
 ämet:cönsectetüer ädipiscing
 elit.  Aliqüäm söllicitüdin
 fäücibüs mäüris ämet.
EOT
            ,
            HordeString::wordwrap($string, 31, "\n", false, true)
        );
        $string = "Lörem ipsüm dölör sit; ämet;  cönsectetüer ädipiscing elit.  Aliqüäm söllicitüdin fäücibüs mäüris ämet.";
        $this->assertEquals(
            <<<EOT
Lörem ipsüm dölör sit;
 ämet;
  cönsectetüer ädipiscing elit.
  Aliqüäm söllicitüdin fäücibüs
 mäüris ämet.
EOT
            ,
            HordeString::wordwrap($string, 31, "\n", false, true)
        );
        $string = "Lörem ipsüm dölör sit; ämet;cönsectetüer ädipiscing elit.  Aliqüäm söllicitüdin fäücibüs mäüris ämet.";
        $this->assertEquals(
            <<<EOT
Lörem ipsüm dölör sit;
 ämet;cönsectetüer ädipiscing
 elit.  Aliqüäm söllicitüdin
 fäücibüs mäüris ämet.
EOT
            ,
            HordeString::wordwrap($string, 31, "\n", false, true)
        );

        $string = 'the path to the configuration file for the components script (default : /Users/janschneider/Source/horde/components/lib/Components/../../config/conf.php)."';
        $this->assertEquals(
            <<<EOT
the path to the configuration file for the components script (default :
      /Users/janschneider/Source/horde/components/lib/Components/../../config/conf.php)."
EOT
            ,
            HordeString::wordwrap($string, 75, "\n      ")
        );
    }

    public function testCommon()
    {
        $this->assertEquals('', HordeString::common('foo', 'bar'));
        $this->assertEquals('foo', HordeString::common('foobar', 'fooxyx'));
        $this->assertEquals('foo', HordeString::common('foo', 'foobar'));
    }

    public function testBug9528()
    {
        $this->assertEquals(
            "<html>",
            HordeString::convertCharset("<html>", 'UTF-8', 'Windows-1258')
        );
    }

    public function testLongStringsBreakUtf8DetectionRegex()
    {
        $string = str_repeat('1 A B', 10000);

        /* Failing test will cause a PHP segfault here. */
        $this->assertTrue(HordeString::validUtf8($string));
    }

    /**
     * @dataProvider validUtf8Provider
     */
    public function testValidUtf8($in)
    {
        $this->assertTrue(HordeString::validUtf8($in));
    }

    public function validUtf8Provider()
    {
        // Examples from:
        // http://www.php.net/manual/en/reference.pcre.pattern.modifiers.php#54805
        return [
            // Valid ASCII
            ["a"],
            // Valid 2 Octet Sequence
            ["\xc3\xb1"],
            // Valid 3 Octet Sequence
            ["\xe2\x82\xa1"],
            // Valid 4 Octet Sequence
            ["\xf0\x90\x8c\xbc"],
            // Bug #11930
            ['ö ä ü ß\n\nMit freundlichen Grüßen'],
            // Bug #11930-2
            ['öäüß']
        ];
    }

    /**
     * @dataProvider invalidUtf8Provider
     */
    public function testInvalidUtf8($in)
    {
        $this->assertFalse(HordeString::validUtf8($in));
    }

    public function invalidUtf8Provider()
    {
        // Examples from:
        // http://www.php.net/manual/en/reference.pcre.pattern.modifiers.php#54805
        return [
            // Invalid 2 Octet Sequence
            ["\xc3\x28"],
            // Invalid Sequence Identifier
            ["\xa0\xa1"],
            // Invalid 3 Octet Sequence (in 2nd Octet)
            ["\xe2\x28\xa1"],
            // Invalid 3 Octet Sequence (in 3rd Octet)
            ["\xe2\x82\x28"],
            // Invalid 4 Octet Sequence (in 2nd Octet)
            ["\xf0\x28\x8c\xbc"],
            // Invalid 4 Octet Sequence (in 3rd Octet)
            ["\xf0\x90\x28\xbc"],
            // Invalid 4 Octet Sequence (in 4th Octet)
            ["\xf0\x28\x8c\x28"],
            // Valid 5 Octet Sequence (but not Unicode!)
            ["\xf8\xa1\xa1\xa1\xa1"],
            // Valid 6 Octet Sequence (but not Unicode!)
            ["\xfc\xa1\xa1\xa1\xa1\xa1"]
        ];
    }
}
