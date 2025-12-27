---
title: Advanced Patterns
description: Complex glob patterns and advanced matching techniques.
---

Complex glob patterns and advanced matching techniques.

## Extended Glob Syntax

### Character Classes

```php
use Cline\Globby\Globby;

// Match specific characters
$files = Globby::find('file[123].txt');  // file1.txt, file2.txt, file3.txt

// Match character range
$files = Globby::find('file[a-z].txt');  // filea.txt through filez.txt

// Negate characters
$files = Globby::find('file[!0-9].txt'); // Exclude numeric
```

### Brace Expansion

```php
// Match alternatives
$files = Globby::find('*.{js,ts,jsx,tsx}');

// Nested braces
$files = Globby::find('{src,lib}/**/*.{js,ts}');

// Numeric ranges
$files = Globby::find('log-{1..10}.txt');
```

### Extended Patterns

```php
// Zero or more directories
$files = Globby::find('**/test/**/*.php');

// Exactly one directory level
$files = Globby::find('src/*/index.php');

// Optional parts
$files = Globby::find('**/*@(.test|.spec).js');
```

## Complex Examples

### Monorepo Patterns

```php
// All package.json files
$packages = Globby::find('packages/*/package.json');

// All source files in packages
$sources = Globby::find('packages/*/src/**/*.{ts,tsx}');

// Tests across all packages
$tests = Globby::find('packages/*/{test,tests,__tests__}/**/*.ts');
```

### Framework-Specific

```php
// Laravel controllers
$controllers = Globby::find('app/Http/Controllers/**/*Controller.php');

// React components
$components = Globby::find('src/components/**/*.{jsx,tsx}');

// Config files
$configs = Globby::find('{config,conf}/**/*.{json,yaml,yml,toml}');
```

### Exclusion Patterns

```php
// All PHP except tests
$files = Globby::find('**/*.php', exclude: [
    '**/*Test.php',
    '**/*TestCase.php',
    'tests/**',
]);

// Source without generated files
$files = Globby::find('src/**/*', exclude: [
    '**/*.generated.*',
    '**/dist/**',
    '**/.cache/**',
]);
```

## Performance Tips

```php
// More specific patterns are faster
Globby::find('src/**/*.php');        // Better
Globby::find('**/*.php');            // Slower (searches everywhere)

// Use exclusions wisely
Globby::find('**/*', exclude: ['vendor/**', 'node_modules/**']);

// Limit depth when possible
Globby::find('*/*.php');             // Only one level deep
Globby::find('**/*.php', depth: 3);  // Max 3 levels
```

## Regular Expression Matching

```php
// When glob isn't enough, use regex
$files = Globby::findByRegex('#/[A-Z][a-z]+Controller\.php$#');

// Combine glob and regex
$files = Globby::find('src/**/*.php')
    ->filter(fn($f) => preg_match('/^[A-Z]/', $f->getFilename()));
```
