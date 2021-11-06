<?php

namespace Horde\Util;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use UnexpectedValueException;
/**
 * The Horde_Util:: class provides generally useful methods.
 *
 * Copyright 1999-2017 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author   Chuck Hagenbuch <chuck@horde.org>
 * @author   Jon Parise <jon@horde.org>
 * @category Horde
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Util
 */
class Util
{
    /**
     * A list of random patterns to use for overwriting purposes.
     * See http://www.cs.auckland.ac.nz/~pgut001/pubs/secure_del.html.
     * We save the random overwrites for efficiency reasons.
     *
     * @var array
     */
    public static array $patterns = [
        "\x55", "\xaa", "\x92\x49\x24", "\x49\x24\x92", "\x24\x92\x49",
        "\x00", "\x11", "\x22", "\x33", "\x44", "\x55", "\x66", "\x77",
        "\x88", "\x99", "\xaa", "\xbb", "\xcc", "\xdd", "\xee", "\xff",
        "\x92\x49\x24", "\x49\x24\x92", "\x24\x92\x49", "\x6d\xb6\xdb",
        "\xb6\xdb\x6d", "\xdb\x6d\xb6",
    ];

    /**
     * Are magic quotes in use?
     *
     * @var bool|null
     */
    protected static $magicquotes = null;

    /**
     * Data used to determine shutdown deletion.
     *
     * @var array
     */
    protected static array $shutdowndata = [
        'paths' => [],
        'secure' => [],
    ];

    /**
     * Has the shutdown method been registered?
     *
     * @var bool
     */
    protected static bool $shutdownreg = false;

    /**
     * Cache for extensionExists().
     *
     * @var array
     */
    protected static array $cache = [];

    /**
     * Checks to see if a value has been set by the script and not by GET,
     * POST, or cookie input. The value being checked MUST be in the global
     * scope.
     *
     * @param string $varname  The variable name to check.
     * @param mixed $default   Default value if the variable isn't present
     *                         or was specified by the user. Defaults to null.
     *
     * @return mixed  $default if the var is in user input or not present,
     *                the variable value otherwise.
     */
    public static function nonInputVar(string $varname, $default = null)
    {
        return (isset($_GET[$varname]) || isset($_POST[$varname]) || isset($_COOKIE[$varname]))
            ? $default
            : ($GLOBALS[$varname] ?? $default);
    }

    /**
     * Returns a hidden form input containing the session name and id.
     *
     * @param bool $append_session  0 = only if needed, 1 = always.
     *
     * @return string  The hidden form input, if needed/requested.
     */
    public static function formInput(bool $append_session = false)
    {
        return (($append_session == true) || !isset($_COOKIE[session_name()]))
            ? '<input type="hidden" name="' . htmlspecialchars(session_name()) . '" value="' . htmlspecialchars(session_id()) . "\" />\n"
            : '';
    }

    /**
     * Prints a hidden form input containing the session name and id.
     *
     * @param bool $append_session  0 = only if needed, 1 = always.
     */
    public static function pformInput(bool $append_session = false): void
    {
        echo self::formInput($append_session);
    }

    /**
     * If magic_quotes_gpc is in use, run stripslashes() on $var.
     *
     * @param mixed $var  The string, or an array of strings, to un-quote.
     *
     * @return mixed  $var, minus any magic quotes.
     */
    public static function dispelMagicQuotes($var)
    {
        if (is_null(self::$magicquotes)) {
            self::$magicquotes = function_exists('get_magic_quotes_gpc') && @get_magic_quotes_gpc();
        }

        if (self::$magicquotes) {
            $var = is_array($var)
                ? array_map([__CLASS__, 'dispelMagicQuotes'], $var)
                : stripslashes($var);
        }

        return $var;
    }

    /**
     * Gets a form variable from GET or POST data, stripped of magic quotes if
     * necessary. If the variable is somehow set in both the GET data and the
     * POST data, the value from the POST data will be returned and the GET
     * value will be ignored.
     *
     * @param string $var      The name of the form variable to look for.
     * @param string $default  The value to return if the variable is not
     *                         there.
     *
     * @return string  The cleaned form variable, or $default.
     */
    public static function getFormData(string $var, ?string $default = null): ?string 
    {
        return (($val = self::getPost($var)) !== null)
            ? $val
            : self::getGet($var, $default);
    }

    /**
     * Gets a form variable from GET data, stripped of magic quotes if
     * necessary. This function will NOT return a POST variable.
     *
     * @param string $var      The name of the form variable to look for.
     * @param string $default  The value to return if the variable is not
     *                         there.
     *
     * @return string  The cleaned form variable, or $default.
     */
    public static function getGet(string $var, ?string $default = null): ?string
    {
        return (isset($_GET[$var]))
            ? self::dispelMagicQuotes($_GET[$var])
            : $default;
    }

    /**
     * Gets a form variable from POST data, stripped of magic quotes if
     * necessary. This function will NOT return a GET variable.
     *
     * @param string $var      The name of the form variable to look for.
     * @param string $default  The value to return if the variable is not
     *                         there.
     *
     * @return string  The cleaned form variable, or $default.
     */
    public static function getPost(string $var, ?string $default = null): string
    {
        return (isset($_POST[$var]))
            ? self::dispelMagicQuotes($_POST[$var])
            : $default;
    }

    /**
     * Creates a temporary filename for the lifetime of the script, and
     * (optionally) registers it to be deleted at request shutdown.
     *
     * @param string $prefix   Prefix to make the temporary name more
     *                         recognizable.
     * @param bool   $delete   Delete the file at the end of the request?
     * @param string $dir      Directory to create the temporary file in.
     * @param bool   $secure   If deleting the file, should we securely delete
     *                         the file by overwriting it with random data?
     *
     * @return string|false   Returns the full path-name to the temporary file.
     *                  Returns false if a temp file could not be created.
     */
    public static function getTempFile(
        string $prefix = '',
        bool $delete = true,
        string $dir = '',
        bool $secure = false
    )
    {
        $tempDir = (empty($dir) || !is_dir($dir))
            ? sys_get_temp_dir()
            : $dir;

        $tempFile = tempnam($tempDir, $prefix);

        // If the file was created, then register it for deletion and return.
        if (empty($tempFile)) {
            return false;
        }

        if ($delete) {
            self::deleteAtShutdown($tempFile, true, $secure);
        }

        return $tempFile;
    }

    /**
     * Creates a temporary filename with a specific extension for the lifetime
     * of the script, and (optionally) registers it to be deleted at request
     * shutdown.
     *
     * @param string $extension  The file extension to use.
     * @param string $prefix     Prefix to make the temporary name more
     *                           recognizable.
     * @param bool   $delete     Delete the file at the end of the request?
     * @param string $dir        Directory to create the temporary file in.
     * @param bool   $secure     If deleting file, should we securely delete
     *                           the file by overwriting it with random data?
     *
     * @return string|false   Returns the full path-name to the temporary file.
     *                  Returns false if a temporary file could not be created.
     */
    public static function getTempFileWithExtension(
        string $extension = '.tmp',
        string $prefix = '',
        bool $delete = true,
        string $dir = '',
        bool $secure = false
    )
    {
        $tempDir = (empty($dir) || !is_dir($dir))
            ? sys_get_temp_dir()
            : $dir;

        if (empty($tempDir)) {
            return false;
        }

        $windows = substr(PHP_OS, 0, 3) == 'WIN';
        $tries = 1;
        do {
            // Get a known, unique temporary file name.
            $sysFileName = tempnam($tempDir, $prefix);
            if ($sysFileName === false) {
                return false;
            }

            // tack on the extension
            $tmpFileName = $sysFileName . $extension;
            if ($sysFileName == $tmpFileName) {
                return $sysFileName;
            }

            // Move or point the created temporary file to the full filename
            // with extension. These calls fail if the new name already
            // exists.
            $fileCreated = ($windows ? @rename($sysFileName, $tmpFileName) : @link($sysFileName, $tmpFileName));
            if ($fileCreated) {
                if (!$windows) {
                    unlink($sysFileName);
                }

                if ($delete) {
                    self::deleteAtShutdown($tmpFileName, true, $secure);
                }

                return $tmpFileName;
            }

            unlink($sysFileName);
        } while (++$tries <= 5);

        return false;
    }

    /**
     * Creates a temporary directory in the system's temporary directory.
     *
     * @param bool   $delete    Delete the temporary directory at the end of
     *                          the request?
     * @param string $temp_dir  Use this temporary directory as the directory
     *                          where the temporary directory will be created.
     *
     * @return string|false  The pathname to the new temporary directory.
     *                 Returns false if directory not created.
     */
    public static function createTempDir(bool $delete = true, string $temp_dir = null)
    {
        if (is_null($temp_dir)) {
            $temp_dir = sys_get_temp_dir();
        }

        if (empty($temp_dir)) {
            return false;
        }

        /* Get the first 8 characters of a random string to use as a temporary
           directory name. */
        do {
            $new_dir = $temp_dir . '/' . substr(base_convert(uniqid(mt_rand()), 16, 36), 0, 8);
        } while (file_exists($new_dir));

        $old_umask = umask(0000);
        if (!mkdir($new_dir, 0700)) {
            $new_dir = false;
        } elseif ($delete) {
            self::deleteAtShutdown($new_dir);
        }
        umask($old_umask);

        return $new_dir;
    }

    /**
     * Returns the canonical path of the string.  Like PHP's built-in
     * realpath() except the directory need not exist on the local server.
     *
     * Algorithim loosely based on code from the Perl File::Spec::Unix module
     * (version 1.5).
     *
     * @param string $path  A file path.
     *
     * @return string  The canonicalized file path.
     */
    public static function realPath(string $path): string
    {
        /* Standardize on UNIX directory separators. */
        if (!strncasecmp(PHP_OS, 'WIN', 3)) {
            $path = str_replace('\\', '/', $path);
        }

        /* xx////xx -> xx/xx
         * xx/././xx -> xx/xx */
        $path = preg_replace(["|/+|", "@(/\.)+(/|\Z(?!\n))@"], ['/', '/'], $path);

        /* ./xx -> xx */
        if ($path != './') {
            $path = preg_replace("|^(\./)+|", '', $path);
        }

        /* /../../xx -> xx */
        $path = preg_replace("|^/(\.\./?)+|", '/', $path);

        /* xx/ -> xx */
        if ($path != '/') {
            $path = preg_replace("|/\Z(?!\n)|", '', $path);
        }

        /* /xx/.. -> / */
        while (strpos($path, '/..') !== false) {
            $path = preg_replace("|/[^/]+/\.\.|", '', $path);
        }

        return empty($path) ? '/' : $path;
    }

    /**
     * Removes given elements at request shutdown.
     *
     * If called with a filename will delete that file at request shutdown; if
     * called with a directory will remove that directory and all files in that
     * directory at request shutdown.
     *
     * If called with no arguments, return all elements to be deleted (this
     * should only be done by Horde_Util::_deleteAtShutdown()).
     *
     * The first time it is called, it initializes the array and registers
     * Horde_Util::_deleteAtShutdown() as a shutdown function - no need to do
     * so manually.
     *
     * The second parameter allows the unregistering of previously registered
     * elements.
     *
     * @param string $filename   The filename to be deleted at the end of the
     *                           request.
     * @param bool   $register   If true, then register the element for
     *                           deletion, otherwise, unregister it.
     * @param bool   $secure     If deleting file, should we securely delete
     *                           the file?
     */
    public static function deleteAtShutdown(
        string $filename,
        bool $register = true,
        bool $secure = false
    )
    {
        /* Initialization of variables and shutdown functions. */
        if (!self::$shutdownreg) {
            register_shutdown_function([__CLASS__, 'shutdown']);
            self::$shutdownreg = true;
        }

        $ptr = &self::$shutdowndata;
        if ($register) {
            $ptr['paths'][$filename] = true;
            if ($secure) {
                $ptr['secure'][$filename] = true;
            }
        } else {
            unset($ptr['paths'][$filename], $ptr['secure'][$filename]);
        }
    }

    /**
     * Deletes registered files at request shutdown.
     *
     * This function should never be called manually; it is registered as a
     * shutdown function by Horde_Util::deleteAtShutdown() and called
     * automatically at the end of the request.
     *
     * Contains code from gpg_functions.php.
     * Copyright 2002-2003 Braverock Ventures
     */
    public static function shutdown()
    {
        $ptr = &self::$shutdowndata;

        foreach (array_keys($ptr['paths']) as $val) {
            if (@is_file($val)) {
                self::_secureDelete($val);
                continue;
            }

            try {
                $it = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($val),
                    RecursiveIteratorIterator::CHILD_FIRST
                );
            } catch (UnexpectedValueException $e) {
                continue;
            }

            while ($it->valid()) {
                if (!$it->isDot()) {
                    if ($it->isDir()) {
                        @rmdir($it->key());
                    } elseif ($it->isFile()) {
                        self::_secureDelete($it->key());
                    } else {
                        @unlink($it->key());
                    }
                }
                $it->next();
            }

            @rmdir($val);
        }
    }

    /**
     * Securely delete the file by overwriting the data with a random
     * string.
     *
     * @param string $file  Filename.
     */
    protected static function _secureDelete(string $file)
    {
        if (isset($ptr['secure'][$file])) {
            $filesize = filesize($file);
            $fp = fopen($file, 'r+');
            foreach (self::$patterns as $pattern) {
                $pattern = substr(str_repeat($pattern, floor($filesize / strlen($pattern)) + 1), 0, $filesize);
                fwrite($fp, $pattern);
                fseek($fp, 0);
            }
            fclose($fp);
        }

        @unlink($file);
    }

    /**
     * Caches the result of extension_loaded() calls.
     *
     * @param string $ext  The extension name.
     *
     * @return bool  Is the extension loaded?
     */
    public static function extensionExists(string $ext): bool
    {
        if (!isset(self::$cache[$ext])) {
            self::$cache[$ext] = extension_loaded($ext);
        }

        return self::$cache[$ext];
    }

    /**
     * Tries to load a PHP extension, behaving correctly for all operating
     * systems.
     *
     * @param string $ext  The extension to load.
     *
     * @return bool     True if the extension is now loaded, false if not.
     *                  True can mean that the extension was already loaded,
     *                  OR was loaded dynamically.
     */
    public static function loadExtension(string $ext): bool
    {
        /* If $ext is already loaded, our work is done. */
        if (self::extensionExists($ext)) {
            return true;
        }

        /* See if we can call dl() at all, by the current ini settings.
         * dl() has been removed in some PHP 5.3 SAPIs. */
        if ((ini_get('enable_dl') != 1) ||
            (ini_get('safe_mode') == 1) ||
            !function_exists('dl')) {
            return false;
        }

        if (!strncasecmp(PHP_OS, 'WIN', 3)) {
            $suffix = 'dll';
        } else {
            switch (PHP_OS) {
            case 'HP-UX':
                $suffix = 'sl';
                break;

            case 'AIX':
                $suffix = 'a';
                break;

            case 'OSX':
                $suffix = 'bundle';
                break;

            default:
                $suffix = 'so';
            }
        }

        return dl($ext . '.' . $suffix) || dl('php_' . $ext . '.' . $suffix);
    }

    /**
     * Utility function to obtain PATH_INFO information.
     *
     * @return string  The PATH_INFO string.
     */
    public static function getPathInfo(): string
    {
        if (isset($_SERVER['PATH_INFO']) &&
            (strpos($_SERVER['SERVER_SOFTWARE'], 'lighttpd') === false)) {
            return $_SERVER['PATH_INFO'];
        } elseif (isset($_SERVER['REQUEST_URI']) &&
                  isset($_SERVER['SCRIPT_NAME'])) {
            $search = HordeString::common($_SERVER['SCRIPT_NAME'], $_SERVER['REQUEST_URI']);
            if (substr($search, -1) == '/') {
                $search = substr($search, 0, -1);
            }
            $search = [$search];
            if (!empty($_SERVER['QUERY_STRING'])) {
                // We can't use QUERY_STRING directly because URL rewriting
                // might add more parameters to the query string than those
                // from the request URI.
                $url = parse_url($_SERVER['REQUEST_URI']);
                if (!empty($url['query'])) {
                    $search[] = '?' . $url['query'];
                }
            }
            $path = str_replace($search, '', $_SERVER['REQUEST_URI']);
            if ($path == '/') {
                $path = '';
            }
            return $path;
        }

        return '';
    }
}
