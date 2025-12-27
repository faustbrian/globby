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

- [Basic Usage](./basic-usage.md) - Common patterns and examples
- [Filtering Options](./filtering-options.md) - Filter results
- [Gitignore Integration](./gitignore-integration.md) - Respect .gitignore
- [Advanced Patterns](./advanced-patterns.md) - Complex matching
