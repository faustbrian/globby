---
title: Gitignore Integration
description: Respect .gitignore rules when matching files.
---

Respect .gitignore rules when matching files.

**Use case:** Finding files while automatically excluding everything in .gitignore, useful for code analysis, search, and deployment.

## Basic Usage

```php
use Cline\Globby\Globby;

// Automatically respect .gitignore
$files = Globby::find('**/*.php', gitignore: true);

// This excludes:
// - vendor/
// - node_modules/
// - .env
// - Any other patterns in .gitignore
```

## Multiple Gitignore Files

```php
// Respects all .gitignore files in the tree
$files = Globby::find('**/*', gitignore: true);

// project/
//   .gitignore           <- respected
//   src/
//     .gitignore         <- also respected
//     components/
//       .gitignore       <- also respected
```

## Global Gitignore

```php
// Include global gitignore (~/.gitignore_global)
$files = Globby::find('**/*', [
    'gitignore' => true,
    'globalGitignore' => true,
]);
```

## Custom Ignore Files

```php
// Use custom ignore file
$files = Globby::find('**/*', ignoreFile: '.deployignore');

// Multiple ignore files
$files = Globby::find('**/*', ignoreFiles: [
    '.gitignore',
    '.npmignore',
    '.deployignore',
]);
```

## Programmatic Ignore Patterns

```php
// Add patterns programmatically
$files = Globby::find('**/*', ignore: [
    'vendor/**',
    'node_modules/**',
    '**/*.log',
    '**/cache/**',
]);

// Combine with gitignore
$files = Globby::find('**/*', [
    'gitignore' => true,
    'ignore' => ['**/temp/**'],  // Additional patterns
]);
```

## Negation Patterns

```php
// Include files that would otherwise be ignored
$files = Globby::find('**/*', [
    'gitignore' => true,
    'ignore' => [
        '!vendor/important-package/**',  // Include this despite gitignore
    ],
]);
```

## Checking If File Is Ignored

```php
use Cline\Globby\Gitignore;

$gitignore = Gitignore::load('/path/to/project');

// Check single file
$gitignore->isIgnored('vendor/autoload.php');  // true
$gitignore->isIgnored('src/App.php');          // false

// Check multiple files
$ignored = $gitignore->filterIgnored($files);
$notIgnored = $gitignore->filterNotIgnored($files);
```
