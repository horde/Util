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
}
