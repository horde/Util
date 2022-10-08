<?php
/**
 * @author     Michael Slusarz <slusarz@horde.org>
 * @license    http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @category   Horde
 * @package    Util
 * @subpackage UnitTests
 */

namespace Horde\Util\Test;

use PHPUnit\Framework\TestCase;
use Horde\Util\Domhtml;
use Horde\Util\HordeString;
use DOMElement;
use DOMNode;

class DomhtmlTest extends TestCase
{
    public function testBug9567()
    {
        $text = <<<EOT
<html>
 <head>
  <meta http-equiv=3DContent-Type content=3D"text/html; charset=3Diso-8859-1">
 </head>
 <body>
  pr=E9parer =E0 vendre d&#8217;ao=FBt&nbsp;;
 </body>
</html>
EOT;

        $expected = "préparer à vendre d’août ;";

        $dom = new Domhtml(quoted_printable_decode($text), 'iso-8859-1');

        $this->assertEquals(
            HordeString::convertCharset($expected, 'UTF-8', 'iso-8859-1'),
            trim($dom->returnBody())
        );

        $this->assertEquals(
            'iso-8859-1',
            $dom->getCharset()
        );

        /* Test auto-detect. */
        $dom = new Domhtml(quoted_printable_decode($text));

        $this->assertEquals(
            HordeString::convertCharset($expected, 'UTF-8', 'iso-8859-1'),
            trim($dom->returnBody())
        );

        $this->assertEquals(
            'iso-8859-1',
            $dom->getCharset()
        );
    }

    public function testBug9714()
    {
        $text = "<html><body>J'ai r=E9ussi J ai r=E9ussi</body></html>";
        $expected = "J'ai réussi J ai réussi";

        $dom = new Domhtml(quoted_printable_decode($text), 'iso-8859-15');
        $this->assertEquals(
            HordeString::convertCharset($expected, 'UTF-8', 'iso-8859-15'),
            trim($dom->returnBody())
        );

        /* iso-8859-15 is not recognized, so UTF-8 is used internally. */
        $this->assertEquals(
            'UTF-8',
            $dom->getCharset()
        );

        /* Test auto-detect. */
        $dom = new Domhtml(quoted_printable_decode($text));

        $this->assertEquals(
            HordeString::convertCharset($expected, 'UTF-8', 'iso-8859-15'),
            trim($dom->returnBody())
        );

        /* iso-8859-1 is used for auto-detection. */
        $this->assertEquals(
            'iso-8859-1',
            $dom->getCharset()
        );
    }

    public function testBug9992()
    {
        $text = base64_decode('dGVzdDogtbno6bvtu+nt/eHpu7797Txicj4K');
        $expected = '<p>test: ľščéťíťéíýáéťžýí<br/></p>';

        $dom = new Domhtml($text, 'iso-8859-2');
        $this->assertEquals(
            HordeString::convertCharset($expected, 'UTF-8', 'iso-8859-2'),
            strtr(trim($dom->returnBody()), ["\n" => ''])
        );

        $this->assertEquals(
            'UTF-8',
            $dom->getCharset()
        );
    }

    public function testIterator()
    {
        $text = file_get_contents(__DIR__ . '/fixtures/domhtml_test.html');
        $dom = new Domhtml($text);

        $tags = [
            'html',
            'body',
            'div',
            'head',
            'title'
        ];

        foreach ($dom as $node) {
            $this->assertInstanceOf(DOMNode::class, $node);
            if ($node instanceof DOMElement) {
                if ($node->tagName != reset($tags)) {
                    $this->fail('Wrong tag name.');
                }
                array_shift($tags);
            }
        }
    }

    public function testHrefSpaces()
    {
        $text = <<<EOT
<html>
 <body>
  <a href="  http://foo.example.com/">Foo</a>
 </body>
</html>
EOT;

        $dom = new Domhtml($text, 'UTF-8');

        foreach ($dom as $val) {
            if (($val instanceof DOMElement) &&
                ($val->tagName == 'a')) {
                $this->assertEquals(
                    '  http://foo.example.com/',
                    $val->getAttribute('href')
                );
            }
        }

        $this->assertEquals(
            'UTF-8',
            $dom->getCharset()
        );
    }

    public function testHeadGeneration()
    {
        $dom = new Domhtml('<div>foo</div>');
        $head = $dom->getHead();

        $this->assertNull($head->previousSibling);

        $this->assertEquals(
            'iso-8859-1',
            $dom->getCharset()
        );
    }

    public function testBodyGeneration()
    {
        $dom = new Domhtml('<div>foo</div>');
        $body = $dom->getBody();

        $this->assertEquals(
            1,
            $body->childNodes->length
        );

        $this->assertEquals(
            'div',
            $body->childNodes->item(0)->tagName
        );
    }

    public function testReturnHtmlCharset()
    {
        $dom = new DomHtml('<html><body><div>préparer à vendre d’août</div></body></html>', 'UTF-8');

        $this->assertEquals(
            $dom->returnHtml(),
            $dom->returnHtml(['charset' => 'iso-8859-1'])
        );
    }

    public function testReturnHtmlMetaCharset()
    {
        $dom = new Domhtml('<html><body><div>foo</div></body></html>', 'UTF-8');

        $this->assertMatchesRegularExpression(
            '/"text\/html; charset=utf-8"/',
            $dom->returnHtml(['metacharset' => true])
        );
    }
}
