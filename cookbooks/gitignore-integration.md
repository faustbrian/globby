# Gitignore Integration

This cookbook covers how to use Globby's gitignore integration for respecting `.gitignore` rules.

## Basic Gitignore Support

Enable gitignore filtering to exclude files that would be ignored by git:

```php
use Cline\Globby\Facades\Globby;

// Respect .gitignore rules
$files = Globby::glob('**/*', ['gitignore' => true]);

// This will exclude:
// - Files listed in .gitignore
// - Files in .gitignore from parent directories
// - Files matching patterns in nested .gitignore files
```

## How It Works

When `gitignore` is enabled, Globby:

1. Finds the `.gitignore` file in the current working directory
2. Searches upward to find the git repository root
3. Collects all `.gitignore` files in the path
4. Scans subdirectories for additional `.gitignore` files
5. Applies all rules in order, with later rules overriding earlier ones

## Check If a Path Is Ignored

You can check if a specific path would be ignored:

```php
// Check if a path is ignored by .gitignore
$isIgnored = Globby::isGitIgnored('/path/to/file.php', [
    'cwd' => '/path/to/project',
]);

if ($isIgnored) {
    echo "This file is ignored by .gitignore";
}
```

## Custom Ignore Files

Use custom ignore files (not just `.gitignore`):

```php
// Use .npmignore rules
$files = Globby::glob('**/*', [
    'ignoreFiles' => '.npmignore',
]);

// Use multiple ignore files
$files = Globby::glob('**/*', [
    'ignoreFiles' => ['.gitignore', '.npmignore', '.dockerignore'],
]);

// Use glob pattern to find ignore files
$files = Globby::glob('**/*', [
    'ignoreFiles' => '**/.ignore',
]);
```

## Check Against Custom Ignore Files

Check if a path matches rules from specific ignore files:

```php
$isIgnored = Globby::isIgnoredByIgnoreFiles(
    '/path/to/file.php',
    ['.npmignore', '.dockerignore'],
    ['cwd' => '/path/to/project']
);
```

## Combining with Other Options

Gitignore filtering works with all other options:

```php
// Gitignore + custom ignore patterns
$files = Globby::glob('**/*.php', [
    'gitignore' => true,
    'ignore' => ['**/*Test.php'],
]);

// Gitignore + dotfiles (note: .gitignore will still exclude dotfiles it lists)
$files = Globby::glob('**/*', [
    'gitignore' => true,
    'dot' => true,
]);

// Gitignore + depth limiting
$files = Globby::glob('**/*', [
    'gitignore' => true,
    'deep' => 3,
]);
```

## Negation in Gitignore

Globby respects gitignore negation patterns:

```gitignore
# .gitignore
*.log
!important.log
```

```php
// important.log will be included, other .log files excluded
$files = Globby::glob('*.log', ['gitignore' => true]);
```

## Directory-Specific Rules

Rules from nested `.gitignore` files apply to their directories:

```
project/
├── .gitignore          # *.log
├── src/
│   ├── .gitignore      # !debug.log  (allows debug.log in src/)
│   └── debug.log       # Included!
└── build.log           # Excluded
```

```php
$files = Globby::glob('**/*.log', [
    'cwd' => 'project',
    'gitignore' => true,
]);
// Returns: ['src/debug.log']
```
