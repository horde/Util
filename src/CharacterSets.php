<?php

declare(strict_types=1);

namespace Horde\Util;

/**
 * Lookup and conversion between character set names for different problem domains
 *
 * Copyright 2022-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author   Ralf Lang <ralf.lang@ralf-lang.de>
 * @category Horde
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Util
 */
class CharacterSets
{
    /**
     * Normalization map for character set aliases to canonical names.
     *
     * This map handles common charset aliases across different systems:
     * - MySQL utf8mb3/utf8mb4 variants → utf-8
     * - Simple utf8 (no dash) → utf-8
     */
    private static array $normalizeMap = [
        'utf8mb3' => 'utf-8',
        'utf8mb4' => 'utf-8',
        'utf8' => 'utf-8',
        // Non-standard charset used when 8-bit body data has no declared
        // encoding (common in older mail software). Treat as Latin-1.
        'unknown-8bit' => 'iso-8859-1',
    ];

    /**
     * Map charset aliases to unambiguous ICU canonical names for UConverter.
     *
     * PHP's UConverter emits "Ambiguous encoding specified" warnings for names
     * that map to multiple ICU converters (e.g. windows-1258 → ibm-5354 or
     * cp1258). Use the canonical name PHP would pick anyway.
     *
     * @see https://www.php.net/manual/en/class.uconverter.php
     */
    private static array $uconverterMap = [
        'big5-hkscs' => 'ibm-1375_P100-2008',
        'shift_jis' => 'ibm-943_P15A-2003',
        'tis-620' => 'windows-874-2000',
        'windows-1258' => 'cp1258',
        'windows-936' => 'windows-936-2000',
        'windows-950' => 'windows-950-2000',
    ];

    /**
     * Normalize a character set identifier to a canonical name.
     *
     * This should be called before passing charset names to any conversion
     * function (iconv, mbstring, UConverter) to handle common aliases.
     *
     * @param string $identifier  The charset identifier to normalize.
     *
     * @return string  The normalized charset identifier.
     */
    public static function normalize(string $identifier): string
    {
        $lower = strtolower($identifier);
        return self::$normalizeMap[$lower] ?? $identifier;
    }

    /**
     * Convert charset identifier to mbstring-compatible name.
     *
     * This applies normalization and any mbstring-specific mappings.
     *
     * @param string $identifier  The charset identifier.
     *
     * @return string  The mbstring-compatible charset name.
     */
    public static function toMbstring(string $identifier): string
    {
        // TODO: Check against mb_list_encoding
        return self::normalize($identifier);
    }

    /**
     * Convert charset identifier to an unambiguous UConverter/ICU name.
     *
     * @param string $identifier  The charset identifier.
     *
     * @return string  The UConverter-compatible charset name.
     */
    public static function toUConverter(string $identifier): string
    {
        $identifier = self::normalize($identifier);
        $lower = strtolower($identifier);

        return self::$uconverterMap[$lower] ?? $identifier;
    }
}
