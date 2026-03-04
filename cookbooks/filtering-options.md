# Filtering Options

This cookbook covers the various filtering options available in Globby.

## Files vs Directories

Control whether to match files, directories, or both:

```php
use Cline\Globby\Facades\Globby;

// Match only files (default behavior)
$files = Globby::glob('*', ['onlyFiles' => true]);

// Match only directories
$dirs = Globby::glob('*', ['onlyDirectories' => true]);

// Match both files and directories
$all = Globby::glob('*', ['onlyFiles' => false, 'onlyDirectories' => false]);
```

## Dotfiles

By default, files starting with a dot are excluded. Enable them with the `dot` option:

```php
// Include dotfiles
$files = Globby::glob('*', ['dot' => true]);

// This will now include .gitignore, .env, etc.
```

## Ignore Patterns

Exclude specific patterns from results:

```php
// Exclude vendor and node_modules
$files = Globby::glob('**/*', [
    'ignore' => ['vendor/**', 'node_modules/**'],
]);

// Exclude test files
$files = Globby::glob('**/*.php', [
    'ignore' => ['**/*Test.php', '**/*Spec.php'],
]);
```

## Depth Limiting

Limit how deep into directories the search goes:

```php
// Only search 2 levels deep
$files = Globby::glob('**/*', ['deep' => 2]);

// First level only
$files = Globby::glob('**/*', ['deep' => 0]);
```

## Absolute Paths

Return absolute paths instead of relative:

```php
$files = Globby::glob('*.php', ['absolute' => true]);

// Results: ['/full/path/to/file.php', ...]
```

## Unique Results

By default, results are deduplicated. Disable if needed:

```php
// Allow duplicate paths (from overlapping patterns)
$files = Globby::glob(['*.php', 'app.php'], ['unique' => false]);
```

## Case Sensitivity

Control case-sensitive matching:

```php
// Case-insensitive matching
$files = Globby::glob('*.PHP', ['caseSensitiveMatch' => false]);

// Will match: file.php, FILE.PHP, File.Php, etc.
```

## Mark Directories

Add trailing slashes to directory paths:

```php
$items = Globby::glob('*', [
    'onlyFiles' => false,
    'markDirectories' => true,
]);

// Results: ['file.txt', 'directory/', ...]
```

## Basename Matching

Match patterns against the file basename only:

```php
// Match 'config.php' anywhere in the tree
$files = Globby::glob('config.php', ['baseNameMatch' => true]);

// Finds: src/config.php, app/config.php, etc.
```

## Directory Expansion

Customize how directories are expanded:

```php
// Expand with specific extensions only
$files = Globby::glob('src', [
    'expandDirectories' => [
        'extensions' => ['php', 'js'],
    ],
]);

// Expand with specific file patterns
$files = Globby::glob('src', [
    'expandDirectories' => [
        'files' => ['*.config.php', '*.routes.php'],
    ],
]);

// Disable expansion entirely
$files = Globby::glob('src', ['expandDirectories' => false]);
```

## Symbolic Links

Control whether symbolic links are followed:

```php
// Don't follow symbolic links
$files = Globby::glob('**/*', ['followSymbolicLinks' => false]);
```

## Error Suppression

Suppress errors from inaccessible directories:

```php
$files = Globby::glob('**/*', ['suppressErrors' => true]);
```
