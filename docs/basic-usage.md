---
title: Basic Usage
description: Common glob patterns and everyday usage examples.
---

Common glob patterns and everyday usage examples.

## Finding Files

```php
use Cline\Globby\Globby;

// Single pattern
$files = Globby::find('*.php');

// Multiple patterns
$files = Globby::find(['*.php', '*.js', '*.ts']);

// With base directory
$files = Globby::find('**/*.php', cwd: '/path/to/project');
```

## Pattern Examples

### By Extension

```php
// Single extension
$php = Globby::find('**/*.php');

// Multiple extensions
$code = Globby::find('**/*.{php,js,ts}');

// Any file
$all = Globby::find('**/*');
```

### By Directory

```php
// Files in src
$src = Globby::find('src/**/*');

// Files in tests
$tests = Globby::find('tests/**/*');

// Multiple directories
$files = Globby::find('{src,lib}/**/*.php');
```

### By Name Pattern

```php
// Files starting with Test
$tests = Globby::find('**/Test*.php');

// Files ending with Test
$tests = Globby::find('**/*Test.php');

// Files containing "config"
$configs = Globby::find('**/*config*');
```

## Working with Results

```php
$files = Globby::find('**/*.php');

// Iterate
foreach ($files as $file) {
    echo $file->getPathname() . "\n";
}

// Convert to array
$paths = $files->toArray();

// Get count
$count = $files->count();

// Filter further
$large = $files->filter(fn($f) => $f->getSize() > 1024);
```

## Exclusions

```php
// Exclude single pattern
$files = Globby::find('**/*.php', exclude: ['vendor/**']);

// Exclude multiple patterns
$files = Globby::find('**/*.php', exclude: [
    'vendor/**',
    'node_modules/**',
    '**/cache/**',
]);
```

## Options

```php
$files = Globby::find('**/*.php', [
    'cwd' => '/path/to/project',     // Base directory
    'dot' => true,                    // Include dotfiles
    'followSymlinks' => false,        // Don't follow symlinks
    'onlyFiles' => true,              // Only files, not directories
    'onlyDirectories' => false,       // Only directories
]);
```
