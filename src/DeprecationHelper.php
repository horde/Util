<?php

declare(strict_types=1);

namespace Horde\Util;

/**
 * Utility for managing deprecations across the Horde framework.
 *
 * Provides helpers for emitting deprecation notices that point to the
 * actual call site in user code, and for maintaining backwards compatibility
 * during the transition period.
 *
 * Copyright 2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author   Dmitry Petrov <dpetrov67@gmail.com>
 * @category Horde
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Util
 */
class DeprecationHelper
{
    /**
     * Files to skip when walking the call stack to find the user-code call site.
     * Autoloader infrastructure (e.g. ClassLoader.php) sits between user code
     * and this class in the backtrace and must be stepped over.
     *
     * @var string[]
     */
    private static array $ignoreFiles = ['ClassLoader.php'];

    /**
     * Emits a deprecation notice and aliases $oldClass to $newClass.
     *
     * @param string $oldClass Fully-qualified name of the deprecated class.
     * @param string $newClass Fully-qualified name of the replacement class.
     * @param bool   $trigger  True: raise E_USER_DEPRECATED (respects error handlers).
     *                         False: write silently to the server error log.
     */
    public static function redirectClass(string $oldClass, string $newClass, bool $trigger = true): void
    {
        $message = sprintf('The %s class is deprecated. Switch to %s', $oldClass, $newClass);

        $location = self::resolveCallSite();
        if ($location) {
            [$file, $line] = $location;
            $message .= " ($file:$line)";
        }

        if ($trigger) {
            trigger_error($message, E_USER_DEPRECATED);
        } else {
            error_log('DEPRECATED: ' . $message);
        }

        class_alias($newClass, $oldClass);
    }

    /**
     * Walks the backtrace to find the first user-code frame after the autoloader boundary.
     *
     * @return array{0: string, 1: int}|null  [$file, $line] of the call site, or null if not found.
     */
    private static function resolveCallSite(): ?array
    {
        $cl = false;

        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $frame) {
            $file = $frame['file'] ?? null;

            if (in_array(basename($file), self::$ignoreFiles, strict: true)) {
                $cl = true;
            } elseif ($cl) {
                return [$file, $frame['line'] ?? null];
            }
        }

        return null;
    }
}
