# Basic Usage

This cookbook covers the fundamental operations of Globby for file matching.

## Simple Pattern Matching

Match files using standard glob patterns:

```php
use Cline\Globby\Facades\Globby;

// Match all PHP files
$files = Globby::glob('*.php');

// Match files in a specific directory
$files = Globby::glob('src/*.php');

// Match with a custom working directory
$files = Globby::glob('*.php', ['cwd' => '/path/to/project']);
```

## Multiple Patterns

Pass an array of patterns to match multiple file types:

```php
// Match PHP and JavaScript files
$files = Globby::glob(['*.php', '*.js']);

// Match across different directories
$files = Globby::glob(['src/*.php', 'tests/*.php', 'config/*.php']);
```

## Recursive Matching

Use the globstar (`**`) pattern to match files recursively:

```php
// Match all PHP files in any subdirectory
$files = Globby::glob('**/*.php');

// Match all files in nested directories
$files = Globby::glob('src/**/*');

// Match specific file types recursively
$files = Globby::glob('**/*.{php,js,ts}');
```

## Negation Patterns

Exclude files using negation patterns (prefixed with `!`):

```php
// Match all PHP files except tests
$files = Globby::glob(['**/*.php', '!**/*Test.php']);

// Match everything except vendor and node_modules
$files = Globby::glob(['**/*', '!vendor/**', '!node_modules/**']);

// Exclude specific files
$files = Globby::glob(['*.txt', '!secret.txt']);
```

## Directory Expansion

Directories are automatically expanded to include all their contents:

```php
// This matches all files in the 'src' directory recursively
$files = Globby::glob('src');

// Equivalent to:
$files = Globby::glob('src/**/*');

// Disable automatic expansion
$files = Globby::glob('src', ['expandDirectories' => false]);
```

## Fluent API

Use the `GlobbyOptions` class for a fluent configuration:

```php
use Cline\Globby\GlobbyManager;
use Cline\Globby\GlobbyOptions;

$manager = new GlobbyManager();

$options = GlobbyOptions::create()
    ->cwd('/path/to/project')
    ->dot(true)
    ->absolute(true);

$files = $manager->globWithOptions('**/*.php', $options);
```

## Streaming Results

For large directories, use streaming to process files one at a time:

```php
$generator = Globby::stream('**/*');

foreach ($generator as $file) {
    // Process each file as it's found
    echo $file . PHP_EOL;
}
```
