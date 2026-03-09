## Table of Contents

1. [Basic Usage](#doc-cookbooks-basic-usage)
2. [Filtering Options](#doc-cookbooks-filtering-options)
3. [Gitignore Integration](#doc-cookbooks-gitignore-integration)
4. [Advanced Patterns](#doc-cookbooks-advanced-patterns)
5. [Overview](#doc-docs-readme)
6. [Advanced Patterns](#doc-docs-advanced-patterns)
7. [Basic Usage](#doc-docs-basic-usage)
8. [Filtering Options](#doc-docs-filtering-options)
9. [Gitignore Integration](#doc-docs-gitignore-integration)
<a id="doc-cookbooks-basic-usage"></a>

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

<a id="doc-cookbooks-filtering-options"></a>

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

<a id="doc-cookbooks-gitignore-integration"></a>

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

<a id="doc-cookbooks-advanced-patterns"></a>

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

<a id="doc-docs-readme"></a>

Globby is a PHP library for file pattern matching using glob patterns, similar to how shells match files.

## Installation

```bash
composer require cline/globby
```

## Basic Usage

```php
use Cline\Globby\Globby;

// Find all PHP files
$files = Globby::find('**/*.php');

// Find files in specific directory
$files = Globby::find('src/**/*.php');

// Find multiple patterns
$files = Globby::find(['**/*.php', '**/*.js']);
```

## Pattern Syntax

| Pattern | Description |
|---------|-------------|
| `*` | Match any characters except `/` |
| `**` | Match any characters including `/` |
| `?` | Match single character |
| `[abc]` | Match characters in brackets |
| `[!abc]` | Match characters not in brackets |
| `{a,b}` | Match any of the patterns |

## Examples

```php
// All PHP files recursively
Globby::find('**/*.php');

// PHP files in src only (not subdirs)
Globby::find('src/*.php');

// Test files
Globby::find('tests/**/*Test.php');

// Config files (json or yaml)
Globby::find('config/*.{json,yaml}');

// All files except vendor
Globby::find('**/*', exclude: ['vendor/**']);
```

## Next Steps

- [Basic Usage](#doc-docs-basic-usage) - Common patterns and examples
- [Filtering Options](#doc-docs-filtering-options) - Filter results
- [Gitignore Integration](#doc-docs-gitignore-integration) - Respect .gitignore
- [Advanced Patterns](#doc-docs-advanced-patterns) - Complex matching

<a id="doc-docs-advanced-patterns"></a>

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

<a id="doc-docs-basic-usage"></a>

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

<a id="doc-docs-filtering-options"></a>

Filter glob results by various criteria.

## By File Type

```php
use Cline\Globby\Globby;

// Only files
$files = Globby::find('**/*', onlyFiles: true);

// Only directories
$dirs = Globby::find('**/*', onlyDirectories: true);

// Both (default)
$all = Globby::find('**/*');
```

## By Size

```php
// Files larger than 1KB
$large = Globby::find('**/*')->filter(
    fn($file) => $file->getSize() > 1024
);

// Files smaller than 1MB
$small = Globby::find('**/*')->filter(
    fn($file) => $file->getSize() < 1024 * 1024
);

// Files between sizes
$medium = Globby::find('**/*')->filter(
    fn($file) => $file->getSize() >= 1024 && $file->getSize() <= 102400
);
```

## By Date

```php
// Modified in last 24 hours
$recent = Globby::find('**/*')->filter(
    fn($file) => $file->getMTime() > time() - 86400
);

// Modified before specific date
$old = Globby::find('**/*')->filter(
    fn($file) => $file->getMTime() < strtotime('2024-01-01')
);
```

## By Content

```php
// PHP files containing a class
$classes = Globby::find('**/*.php')->filter(
    fn($file) => str_contains(file_get_contents($file), 'class ')
);

// Files with specific line count
$short = Globby::find('**/*.php')->filter(function ($file) {
    $lines = count(file($file));
    return $lines < 100;
});
```

## Combining Filters

```php
$files = Globby::find('**/*.php')
    ->filter(fn($f) => $f->getSize() > 0)           // Non-empty
    ->filter(fn($f) => $f->getMTime() > $cutoff)    // Recent
    ->filter(fn($f) => !str_contains($f, 'test'));  // Not tests
```

## Custom Filters

```php
use Cline\Globby\Filter;

// Create reusable filter
$phpFilter = Filter::extension('php')
    ->and(Filter::minSize(100))
    ->and(Filter::modifiedAfter('-1 week'));

$files = Globby::find('**/*', filter: $phpFilter);
```

## Sorting Results

```php
$files = Globby::find('**/*.php');

// Sort by name
$sorted = $files->sortByName();

// Sort by size
$sorted = $files->sortBySize();

// Sort by modification time
$sorted = $files->sortByMTime();

// Custom sort
$sorted = $files->sort(fn($a, $b) => $a->getSize() <=> $b->getSize());
```

<a id="doc-docs-gitignore-integration"></a>

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
