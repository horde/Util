# Upgrading to Util 3.0 (PSR-4)

## Overview

Version 3.0 introduces a modern PSR-4 implementation with strict types and improved charset handling.
The legacy PSR-0 `lib/` variants remain for backward compatibility. Callers are encouraged to migrate.

## Requirements

- PHP 8.2 or later
- mbstring extension (mandatory)
- iconv extension or intl extension (strongly encouraged if interfacing with ActiveSync or calendaring code)

## Key Changes

### Deprecated Functions Removed

The following deprecated PHP functions have been removed from the modern implementation:

- `utf8_encode()` (deprecated in PHP 8.2)
- `utf8_decode()` (deprecated in PHP 8.2)

Code using `Horde\Util\HordeString` will use modern charset conversion via iconv, UConverter, or mbstring.

### Charset Conversion Priority Changed

The charset conversion priority has changed to better handle Windows codepages:

**Old priority (Horde_String):**
1. mbstring
2. iconv

**New priority (Horde\Util\HordeString):**
1. iconv (preferred for Windows Arabic codepage support)
2. UConverter (fallback for unsupported codepages or if iconv missing)
3. mbstring (secondary fallback)

This change resolves GitHub issues #14 (utf8 deprecation) and #22 (Arabic codepage).

### Strict Type Declarations

All PSR-4 classes use `declare(strict_types=1);` and type hints. This means:

- Method parameters enforce types strictly
- Return types are enforced
- No automatic type coercion

## Breaking Changes

### Type Signature Incompatibility with Horde_Variables

**CRITICAL:** If your code has type hints expecting `Horde_Variables`, it will not automatically accept `Horde\Util\Variables`:

```php
// This function expects PSR-0 class
function processForm(Horde_Variables $vars) {
    // ...
}

// FAILS at runtime with TypeError
$modernVars = new Horde\Util\Variables($_POST);
processForm($modernVars);  // TypeError: must be Horde_Variables, Horde\Util\Variables given
```

**This is not a bug.** These are distinct classes with different namespaces and type identities.

#### Impact

- **Method signatures:** Any method with `Horde_Variables` type hint will reject `Horde\Util\Variables`
- **instanceof checks:** `$vars instanceof Horde_Variables` returns false for PSR-4 instances
- **Class name strings:** Code checking `get_class($vars) === 'Horde_Variables'` will fail
- **Serialization:** Serialized PSR-0 objects cannot be unserialized as PSR-4 objects

#### Migration Options

**Option 1: Accept both types (transitional)**
```php
// Before
function processForm(Horde_Variables $vars) { }

// After
function processForm(Horde_Variables|Horde\Util\Variables $vars) { }
```

**Option 2: Migrate to PSR-4 completely**
```php
// Before
use Horde_Variables;
function processForm(Horde_Variables $vars) { }

// After
use Horde\Util\Variables;
function processForm(Variables $vars) { }
```

### Charset Conversion Error Handling

The modern implementation throws more specific exceptions for charset conversion failures:

```php
// Old (Horde_String) - silent failure, returns original
$result = Horde_String::convertCharset($text, 'INVALID-CHARSET', 'UTF-8');
// Returns $text unchanged

// New (Horde\Util\HordeString) - verbose exceptions
try {
    $result = HordeString::convertCharset($text, 'INVALID-CHARSET', 'UTF-8');
} catch (RuntimeException $e) {
    // Exception with detailed error message
}
```

If you depend on silent fallback behavior, wrap calls in try-catch.

### Property Name Changes (Internal)

PSR-4 classes use modern property naming without leading underscores:

```php
// Horde_Variables (PSR-0)
protected $_vars;
protected $_expected;
protected $_sanitized;

// Horde\Util\Variables (PSR-4)
protected $vars;
protected array $expected;
protected bool $sanitized;
```

**Impact:** If you extend `Horde_Variables` and access these properties, you must update to PSR-4 property names.

## Class Name Mapping

| PSR-0 (lib/) | PSR-4 (src/) | Notes |
|-------------|-------------|-------|
| `Horde_String` | `Horde\Util\HordeString` | Renamed to avoid PHP reserved word |
| `Horde_Array` | `Horde\Util\ArrayUtils` | Renamed to avoid PHP reserved word |
| `Horde_Util` | `Horde\Util\Util` | Direct mapping |
| `Horde_Variables` | `Horde\Util\Variables` | See type signature warnings |
| `Horde_Domhtml` | `Horde\Util\Domhtml` | Direct mapping |
| `Horde_String_Transliterate` | `Horde\Util\StringTransliterate` | Direct mapping |

**Note:** `Horde_String` becomes `HordeString` in PSR-4 because `String` is a reserved word in PHP.

## Migration Strategies

### Strategy 1: Gradual Migration

Migrate file by file to PSR-4:

```php
// Update imports
use Horde\Util\HordeString;
use Horde\Util\Variables;
use Horde\Util\Util;

// Update code
$text = HordeString::lower($text, true, 'UTF-8');
$vars = new Variables($_POST);
```

**Watch for type hint incompatibilities** when mixing PSR-0 and PSR-4.

### Strategy 2: Complete Migration

Migrate entire codebase at once:

1. Search and replace imports:
   ```bash
   # Example: Horde_String to HordeString
   find src/ -type f -name "*.php" -exec sed -i 's/Horde_String/Horde\\Util\\HordeString/g' {} +
   ```

2. Add use statements:
   ```php
   use Horde\Util\HordeString;
   use Horde\Util\Variables;
   use Horde\Util\Util;
   ```

3. Update type hints globally

4. Add strict_types declarations

5. Run full test suite

## Common Patterns

### String Operations

```php
// Old
use Horde_String;

$lower = Horde_String::lower($text, true, 'UTF-8');
$upper = Horde_String::upper($text, true, 'UTF-8');
$len = Horde_String::length($text, 'UTF-8');
$converted = Horde_String::convertCharset($text, 'ISO-8859-1', 'UTF-8');

// New
use Horde\Util\HordeString;

$lower = HordeString::lower($text, true, 'UTF-8');
$upper = HordeString::upper($text, true, 'UTF-8');
$len = HordeString::length($text, 'UTF-8');
$converted = HordeString::convertCharset($text, 'ISO-8859-1', 'UTF-8');
```

### Form Variables

```php
// Old
use Horde_Variables;

$vars = Horde_Variables::getDefaultVariables();
$name = $vars->get('name', 'default');
$vars->set('processed', true);

// New
use Horde\Util\Variables;

$vars = Variables::getDefaultVariables();
$name = $vars->get('name', 'default');
$vars->set('processed', true);
```

**CRITICAL:** If passing to functions with `Horde_Variables` type hints, use PSR-0 version.

### Array Operations

```php
// Old
use Horde_Array;

$sorted = Horde_Array::valueSortHelper($array, $key);

// New
use Horde\Util\ArrayUtils;

$sorted = ArrayUtils::valueSortHelper($array, $key);
```

### Utility Functions

```php
// Old
use Horde_Util;

$temp = Horde_Util::getTempFile('prefix');
$exists = Horde_Util::extensionExists('iconv');

// New
use Horde\Util\Util;

$temp = Util::getTempFile('prefix');
$exists = Util::extensionExists('iconv');
```

## Charset Conversion Details

### Handling Unsupported Codepages

The modern implementation uses UConverter as a fallback for codepages not supported by iconv:

```php
use Horde\Util\HordeString;

// This now works on Windows with intl extension
$arabic = HordeString::convertCharset($text, 'WINDOWS-1256', 'UTF-8');
```

Without UConverter, this would fall back to mbstring or throw an exception.

### Handling Conversion Failures

The modern implementation throws exceptions on conversion failures:

```php
use Horde\Util\HordeString;

try {
    $result = HordeString::convertCharset($text, 'FROM-CHARSET', 'TO-CHARSET');
} catch (RuntimeException $e) {
    // Log error or use fallback
    error_log('Charset conversion failed: ' . $e->getMessage());
    $result = $text; // Use original
}
```

The exception messages include details about:
- Which extension was attempted (iconv, UConverter, mbstring)
- The specific error encountered
- The charsets involved

## Troubleshooting

### TypeError: must be Horde_Variables

**Symptom:**
```
TypeError: Argument 1 passed to MyClass::processForm() must be an instance of Horde_Variables,
instance of Horde\Util\Variables given
```

**Cause:** Type hint expects PSR-0 class but received PSR-4 class.

**Solution:** Choose one:
1. Use PSR-0 class: `new Horde_Variables($_POST)`
2. Remove type hint: `function processForm($vars)`
3. Accept both types: `function processForm(Horde_Variables|Horde\Util\Variables $vars)`
4. Migrate caller to PSR-4: Update all code to use `Horde\Util\Variables`

### Charset Conversion Throws Exceptions

**Symptom:**
```
RuntimeException: iconv conversion failed for SOME-CHARSET to UTF-8
```

**Cause:** Modern implementation throws exceptions instead of silently failing.

**Solution:**
```php
// Wrap in try-catch
try {
    $result = HordeString::convertCharset($text, $from, $to);
} catch (RuntimeException $e) {
    // Handle error or use original
    $result = $text;
}
```

### Cannot Extend PSR-4 Class

**Symptom:**
```
Fatal error: Cannot access protected property Horde\Util\Variables::$vars
```

**Cause:** Property names changed in PSR-4 (no leading underscore).

**Solution:** Update property access:
```php
// Old
class MyVars extends Horde_Variables {
    public function custom() {
        return $this->_vars['key'];
    }
}

// New
class MyVars extends Horde\Util\Variables {
    public function custom() {
        return $this->vars['key'];
    }
}
```

### Serialized Objects Incompatible

**Symptom:** Cannot unserialize `Horde_Variables` as `Horde\Util\Variables` or vice versa.

**Cause:** Different class names in serialized data.

**Solution:**
1. Clear old serialized data
2. Re-serialize with new class
3. Or implement `__serialize()` / `__unserialize()` with compatibility layer

## Future Direction

### Variables Class is Deprecated for New Code

The `Variables` class (both PSR-0 and PSR-4) is a legacy pattern for accessing form input. For new code, use PSR-7 request objects from `horde/http`:

```php
// Old pattern (still supported)
use Horde\Util\Variables;
$vars = new Variables($_POST);
$name = $vars->get('name');

// Modern pattern (recommended)
use Psr\Http\Message\ServerRequestInterface;

function handleRequest(ServerRequestInterface $request) {
    $post = $request->getParsedBody();
    $name = $post['name'] ?? null;
}
```

### Benefits of PSR-7 Request/Response

- **Standard interface:** Works with any PSR-7 compatible HTTP client/server
- **Immutability:** Request objects cannot be modified accidentally
- **Better testing:** Easy to create test requests
- **Middleware support:** Integrates with PSR-15 middleware
- **Type safety:** Strong typing throughout

### Migration Path

1. **Short term:** Migrate from `Horde_Variables` to `Horde\Util\Variables`
2. **Medium term:** Use `Variables` for form processing, PSR-7 for HTTP
3. **Long term:** Migrate to PSR-7 requests and eliminate `Variables` usage

The `Util` library will continue to provide `Variables` for backward compatibility, but new applications should prefer PSR-7 patterns.

## Testing Your Migration

### Basic Smoke Test

```php
<?php
declare(strict_types=1);

use Horde\Util\HordeString;
use Horde\Util\Variables;
use Horde\Util\Util;

// Test string operations
assert(HordeString::lower('TEST', true, 'UTF-8') === 'test');
assert(HordeString::upper('test', true, 'UTF-8') === 'TEST');
assert(HordeString::length('test', 'UTF-8') === 4);

// Test charset conversion
$converted = HordeString::convertCharset('test', 'UTF-8', 'ISO-8859-1');
assert(is_string($converted));

// Test variables
$vars = new Variables(['name' => 'value']);
assert($vars->get('name') === 'value');
assert($vars->exists('name') === true);

// Test utils
$temp = Util::getTempFile('test');
assert(file_exists($temp));
unlink($temp);

echo "All tests passed\n";
```

### Type Hint Compatibility Test

```php
<?php
declare(strict_types=1);

use Horde\Util\Variables;

function acceptsOldType(Horde_Variables $vars): void {
    echo "Received PSR-0: " . get_class($vars) . "\n";
}

function acceptsNewType(Variables $vars): void {
    echo "Received PSR-4: " . get_class($vars) . "\n";
}

function acceptsBothTypes(Horde_Variables|Variables $vars): void {
    echo "Received: " . get_class($vars) . "\n";
}

$psr0 = new Horde_Variables(['key' => 'value']);
$psr4 = new Variables(['key' => 'value']);

acceptsOldType($psr0);   // Works
// acceptsOldType($psr4); // TypeError
acceptsNewType($psr4);   // Works
// acceptsNewType($psr0); // TypeError
acceptsBothTypes($psr0); // Works
acceptsBothTypes($psr4); // Works
```

## Getting Help

- **GitHub Issues:** https://github.com/horde/Util/issues
- **Mailing List:** dev@lists.horde.org
- **Documentation:** https://www.horde.org/libraries/Horde_Util

## Version History

- **3.0.0-beta3** (2026-03): Removed utf8_encode/decode, improved error handling
- **3.0.0-beta2** (2026-03): Fixed unsupported codepage handling in PHP 8.x
- **3.0.0-beta1** (2026-03): Initial PSR-4 implementation with strict types
- **2.x** (Horde 5): Legacy PSR-0 implementation
