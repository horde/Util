<?php

declare(strict_types=1);

/**
 * Copyright 2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 */

namespace Horde\Util\Test;

use Horde\Util\CharacterSets;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the CharacterSets class.
 *
 * @author   Ralf Lang <ralf.lang@ralf-lang.de>
 * @category Horde
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Util
 */
#[CoversClass(CharacterSets::class)]
class CharacterSetsTest extends TestCase
{
    public function testNormalizeUtf8mb4(): void
    {
        $this->assertEquals('utf-8', CharacterSets::normalize('utf8mb4'));
    }

    public function testNormalizeUtf8mb4CaseInsensitive(): void
    {
        $this->assertEquals('utf-8', CharacterSets::normalize('UTF8MB4'));
        $this->assertEquals('utf-8', CharacterSets::normalize('Utf8Mb4'));
    }

    public function testNormalizeUtf8mb3(): void
    {
        $this->assertEquals('utf-8', CharacterSets::normalize('utf8mb3'));
    }

    public function testNormalizeUtf8NoDash(): void
    {
        $this->assertEquals('utf-8', CharacterSets::normalize('utf8'));
    }

    public function testNormalizeUtf8WithDash(): void
    {
        // utf-8 is already canonical, should return unchanged
        $this->assertEquals('utf-8', CharacterSets::normalize('utf-8'));
    }

    public function testNormalizeUnknownCharset(): void
    {
        // Unknown charsets should pass through unchanged
        $this->assertEquals('iso-8859-1', CharacterSets::normalize('iso-8859-1'));
        $this->assertEquals('windows-1252', CharacterSets::normalize('windows-1252'));
    }

    public function testNormalizeUnknown8bit(): void
    {
        $this->assertEquals('iso-8859-1', CharacterSets::normalize('unknown-8bit'));
        $this->assertEquals('iso-8859-1', CharacterSets::normalize('UNKNOWN-8BIT'));
    }

    public function testToMbstring(): void
    {
        // toMbstring should normalize utf8mb4 → utf-8
        $this->assertEquals('utf-8', CharacterSets::toMbstring('utf8mb4'));
        $this->assertEquals('utf-8', CharacterSets::toMbstring('UTF8MB4'));
    }

    public function testToMbstringPreservesOtherCharsets(): void
    {
        // Non-normalized charsets should pass through
        $this->assertEquals('iso-8859-1', CharacterSets::toMbstring('iso-8859-1'));
    }
}
