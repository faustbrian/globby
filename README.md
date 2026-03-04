[![GitHub Workflow Status][ico-tests]][link-tests]
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

------

# Globby

A user-friendly glob matching library for PHP, inspired by [sindresorhus/globby](https://github.com/sindresorhus/globby). Features multiple pattern support, negation patterns, gitignore integration, and a fluent configuration API.

## Requirements

> **Requires [PHP 8.4+](https://php.net/releases/)**

## Installation

```bash
composer require cline/globby
```

## Documentation

- **[Basic Usage](cookbooks/basic-usage.md)** - Pattern matching fundamentals, multiple patterns, negation, and streaming
- **[Filtering Options](cookbooks/filtering-options.md)** - Files vs directories, dotfiles, depth limiting, and path formatting
- **[Gitignore Integration](cookbooks/gitignore-integration.md)** - Respecting .gitignore rules and custom ignore files
- **[Advanced Patterns](cookbooks/advanced-patterns.md)** - Brace expansion, character classes, globstar, and escaping

## Supported Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `cwd` | `string` | `getcwd()` | Current working directory |
| `expandDirectories` | `bool\|array` | `true` | Auto-expand directories to glob contents |
| `gitignore` | `bool` | `false` | Respect .gitignore rules |
| `ignoreFiles` | `string\|array` | `null` | Custom ignore file patterns |
| `ignore` | `array` | `[]` | Additional patterns to ignore |
| `onlyFiles` | `bool` | `true` | Match only files |
| `onlyDirectories` | `bool` | `false` | Match only directories |
| `dot` | `bool` | `false` | Include dotfiles |
| `deep` | `int` | `null` | Maximum traversal depth |
| `followSymbolicLinks` | `bool` | `true` | Follow symbolic links |
| `suppressErrors` | `bool` | `false` | Suppress file system errors |
| `absolute` | `bool` | `false` | Return absolute paths |
| `unique` | `bool` | `true` | Deduplicate results |
| `markDirectories` | `bool` | `false` | Add trailing slash to directories |
| `caseSensitiveMatch` | `bool` | `true` | Case-sensitive pattern matching |
| `baseNameMatch` | `bool` | `false` | Match patterns against basename only |
| `throwErrorOnBrokenSymbolicLink` | `bool` | `false` | Throw error on broken symlinks |
| `objectMode` | `bool` | `false` | Return GlobEntry objects instead of strings |
| `stats` | `bool` | `false` | Include file statistics (implies objectMode) |
| `fs` | `FileSystemAdapter` | `null` | Custom file system adapter |

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please use the [GitHub security reporting form][link-security] rather than the issue queue.

## Credits

- [Brian Faust][link-maintainer]
- [All Contributors][link-contributors]

## License

The MIT License. Please see [License File](LICENSE.md) for more information.

[ico-tests]: https://github.com/faustbrian/globby/actions/workflows/quality-assurance.yaml/badge.svg
[ico-version]: https://img.shields.io/packagist/v/cline/globby.svg
[ico-license]: https://img.shields.io/badge/License-MIT-green.svg
[ico-downloads]: https://img.shields.io/packagist/dt/cline/globby.svg

[link-tests]: https://github.com/faustbrian/globby/actions
[link-packagist]: https://packagist.org/packages/cline/globby
[link-downloads]: https://packagist.org/packages/cline/globby
[link-security]: https://github.com/faustbrian/globby/security
[link-maintainer]: https://github.com/faustbrian
[link-contributors]: ../../contributors
