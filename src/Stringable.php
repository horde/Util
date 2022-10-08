<?php

declare(strict_types=1);

namespace Horde\Util;

use Exception;
use PEAR_Error;
use Horde_Imap_Client_Utf7imap;
use Horde_Imap_Client_Exception;
use ValueError;
use InvalidArgumentException;
use Stringable as StringableInterface;

/**
 * OO wrapper for HordeString for charset and locale safe string manipulation.
 *
 * TODO: Change signatures, delegate calls to HordeString.
 *
 * Copyright 2003-2017 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @todo Split up in Stringable_Multibyte for multibyte-safe methods and
 *       Stringable_Locale for locale-safe methods.
 *
 * @author   Ralf Lang <ralf.lang@ralf-lang.de>
 * @category Horde
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Util
 */
class Stringable
{
    public function __construct(private string|StringableInterface $string)
    {
        if ($string instanceof StringableInterface) {
            $this->string = (string) $string;
        }
    }

    public function __toString(): string
    {
        return $this->string;
    }
}
