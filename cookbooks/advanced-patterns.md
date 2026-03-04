# Advanced Patterns

This cookbook covers advanced glob pattern usage in Globby.

## Brace Expansion

Match multiple alternatives using braces:

```php
use Cline\Globby\Facades\Globby;

// Match specific extensions
$files = Globby::glob('*.{php,js,ts}');

// Match multiple directories
$files = Globby::glob('{src,lib,app}/**/*.php');

// Match alternative filenames
$files = Globby::glob('{config,settings,options}.php');

// Nested braces
$files = Globby::glob('{src,lib}/{models,controllers}/*.php');
```

## Character Classes

Match single characters from a set:

```php
// Match file1.php, file2.php, file3.php
$files = Globby::glob('file[123].php');

// Match using ranges
$files = Globby::glob('file[0-9].php');

// Match letters
$files = Globby::glob('[a-z]*.php');

// Negated character class
$files = Globby::glob('file[!0-9].php'); // Not a digit
```

## Single Character Wildcard

Use `?` to match exactly one character:

```php
// Match file1.php, fileA.php, but not file12.php
$files = Globby::glob('file?.php');

// Multiple single character wildcards
$files = Globby::glob('????.php'); // Four character names
```

## Globstar Patterns

Use `**` for recursive directory matching:

```php
// Match at any depth
$files = Globby::glob('**/config.php');

// Match in specific subdirectory structures
$files = Globby::glob('src/**/tests/*.php');

// Match everything under a path
$files = Globby::glob('vendor/**');

// ** at the start matches current directory too
$files = Globby::glob('**/*.php'); // Includes *.php in cwd
```

## Complex Negation

Advanced negation pattern strategies:

```php
// Exclude specific directories but include subdirectories
$files = Globby::glob([
    '**/*.php',
    '!vendor/**',
    '!node_modules/**',
]);

// Include everything, then exclude, then re-include
$files = Globby::glob([
    '**/*',
    '!**/*.test.php',
    '!**/*.spec.php',
]);

// Negation-only patterns (matches all except specified)
$files = Globby::glob(['!*.log', '!*.tmp']);
```

## Pattern Priority

Later patterns override earlier ones:

```php
// First includes all PHP, then excludes tests
$files = Globby::glob([
    '**/*.php',        // Include all PHP
    '!**/Test*.php',   // Exclude test files
]);

// Order matters for negations
$files = Globby::glob([
    '**/*',
    '!vendor/**',
    'vendor/important/**', // This WON'T re-include vendor files
]);
```

## Escaping Special Characters

Use backslashes to match literal special characters:

```php
// Match files with brackets in name
$files = Globby::glob('file\\[1\\].txt');

// Match files with braces
$files = Globby::glob('config\\{production\\}.php');

// Use convertPathToPattern for dynamic escaping
$path = 'file[1].txt';
$pattern = Globby::convertPathToPattern($path);
// Returns: 'file\[1\].txt'
```

## Check If Pattern Is Dynamic

Determine if a pattern contains glob characters:

```php
Globby::isDynamicPattern('*.php');        // true
Globby::isDynamicPattern('file.php');     // false
Globby::isDynamicPattern('**/*.php');     // true
Globby::isDynamicPattern('src/file.php'); // false
Globby::isDynamicPattern('{a,b}.php');    // true
```

## Generate Glob Tasks

Get structured task data for integration with other tools:

```php
$tasks = Globby::generateGlobTasks(['*.php', '!test.php'], [
    'cwd' => '/path/to/project',
]);

// Returns:
// [
//     [
//         'patterns' => ['*.php'],
//         'options' => [
//             'cwd' => '/path/to/project',
//             'negative' => ['test.php'],
//             ...
//         ],
//     ],
// ]
```

## Combining Patterns Effectively

Best practices for complex pattern combinations:

```php
// Source files excluding generated code
$sourceFiles = Globby::glob([
    'src/**/*.php',
    'app/**/*.php',
    '!**/*.generated.php',
    '!**/cache/**',
], ['gitignore' => true]);

// Asset files with specific extensions
$assets = Globby::glob([
    'public/**/*.{css,js,png,jpg,svg}',
    '!public/vendor/**',
]);

// Config files across the project
$configs = Globby::glob([
    '**/{config,configuration}.{php,json,yaml,yml}',
    '**/config/**/*.php',
]);
```
