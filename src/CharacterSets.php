<?php

declare(strict_types=1);

namespace Horde\Util;

/**
 * Lookup and conversion between character set names for different problem domains
 *
 * Copyright 2022-2022 Horde LLC (http://www.horde.org/)
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
    private static array $toMbString = [
        'utf8mb3' => 'utf-8',
        'utf8mb4' => 'utf-8',
        'utf8' => 'utf-8',        
    ];

    public static function toMbstring(string $identifier): string
    {
        // TODO: Check against mb_list_encoding
        return self::$toMbString[$identifier] ?? $identifier;
    }
}
