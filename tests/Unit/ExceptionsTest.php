<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Globby\Exceptions\BrokenSymbolicLinkException;
use Cline\Globby\Exceptions\CannotStatFileException;
use Cline\Globby\Exceptions\FileNotFoundException;
use Cline\Globby\Exceptions\FileUnreadableException;
use Cline\Globby\Exceptions\GlobbyException;
use Cline\Globby\Exceptions\InvalidPatternException;
use Cline\Globby\Exceptions\InvalidPatternTypeException;
use Cline\Globby\Exceptions\PathNotDirectoryException;

describe('BrokenSymbolicLinkException', function (): void {
    describe('Happy Path', function (): void {
        test('creates exception with forPath() factory method', function (): void {
            $exception = BrokenSymbolicLinkException::forPath('/path/to/broken/symlink');

            expect($exception)->toBeInstanceOf(BrokenSymbolicLinkException::class);
            expect($exception->getMessage())->toBe('Broken symbolic link: /path/to/broken/symlink');
        });

        test('message contains provided path', function (): void {
            $path = '/tmp/broken/link.txt';
            $exception = BrokenSymbolicLinkException::forPath($path);

            expect($exception->getMessage())->toContain($path);
        });

        test('implements GlobbyException interface', function (): void {
            $exception = BrokenSymbolicLinkException::forPath('/path');

            expect($exception)->toBeInstanceOf(GlobbyException::class);
        });

        test('extends RuntimeException', function (): void {
            $exception = BrokenSymbolicLinkException::forPath('/path');

            expect($exception)->toBeInstanceOf(RuntimeException::class);
        });
    });
});

describe('CannotStatFileException', function (): void {
    describe('Happy Path', function (): void {
        test('creates exception with forPath() factory method', function (): void {
            $exception = CannotStatFileException::forPath('/path/to/file.txt');

            expect($exception)->toBeInstanceOf(CannotStatFileException::class);
            expect($exception->getMessage())->toBe('Cannot stat file: /path/to/file.txt');
        });

        test('message contains provided path', function (): void {
            $path = '/var/log/system.log';
            $exception = CannotStatFileException::forPath($path);

            expect($exception->getMessage())->toContain($path);
        });

        test('implements GlobbyException interface', function (): void {
            $exception = CannotStatFileException::forPath('/path');

            expect($exception)->toBeInstanceOf(GlobbyException::class);
        });

        test('extends RuntimeException', function (): void {
            $exception = CannotStatFileException::forPath('/path');

            expect($exception)->toBeInstanceOf(RuntimeException::class);
        });
    });
});

describe('FileNotFoundException', function (): void {
    describe('Happy Path', function (): void {
        test('creates exception with forPath() factory method', function (): void {
            $exception = FileNotFoundException::forPath('/path/to/missing.txt');

            expect($exception)->toBeInstanceOf(FileNotFoundException::class);
            expect($exception->getMessage())->toBe('File not found: /path/to/missing.txt');
        });

        test('message contains provided path', function (): void {
            $path = '/home/user/documents/file.pdf';
            $exception = FileNotFoundException::forPath($path);

            expect($exception->getMessage())->toContain($path);
        });

        test('implements GlobbyException interface', function (): void {
            $exception = FileNotFoundException::forPath('/path');

            expect($exception)->toBeInstanceOf(GlobbyException::class);
        });

        test('extends RuntimeException', function (): void {
            $exception = FileNotFoundException::forPath('/path');

            expect($exception)->toBeInstanceOf(RuntimeException::class);
        });
    });
});

describe('FileUnreadableException', function (): void {
    describe('Happy Path', function (): void {
        test('creates exception with forPath() factory method', function (): void {
            $exception = FileUnreadableException::forPath('/path/to/unreadable.txt');

            expect($exception)->toBeInstanceOf(FileUnreadableException::class);
            expect($exception->getMessage())->toBe('Cannot read file: /path/to/unreadable.txt');
        });

        test('message contains provided path', function (): void {
            $path = '/etc/shadow';
            $exception = FileUnreadableException::forPath($path);

            expect($exception->getMessage())->toContain($path);
        });

        test('implements GlobbyException interface', function (): void {
            $exception = FileUnreadableException::forPath('/path');

            expect($exception)->toBeInstanceOf(GlobbyException::class);
        });

        test('extends RuntimeException', function (): void {
            $exception = FileUnreadableException::forPath('/path');

            expect($exception)->toBeInstanceOf(RuntimeException::class);
        });
    });
});

describe('InvalidPatternException', function (): void {
    describe('Happy Path', function (): void {
        test('creates exception with empty() factory method', function (): void {
            $exception = InvalidPatternException::empty();

            expect($exception)->toBeInstanceOf(InvalidPatternException::class);
            expect($exception->getMessage())->toBe('Glob pattern cannot be empty.');
        });

        test('message describes empty pattern error', function (): void {
            $exception = InvalidPatternException::empty();

            expect($exception->getMessage())->toContain('pattern');
            expect($exception->getMessage())->toContain('empty');
        });

        test('implements GlobbyException interface', function (): void {
            $exception = InvalidPatternException::empty();

            expect($exception)->toBeInstanceOf(GlobbyException::class);
        });

        test('extends InvalidArgumentException', function (): void {
            $exception = InvalidPatternException::empty();

            expect($exception)->toBeInstanceOf(InvalidArgumentException::class);
        });
    });
});

describe('InvalidPatternTypeException', function (): void {
    describe('Happy Path', function (): void {
        test('creates exception with forType() factory method', function (): void {
            $exception = InvalidPatternTypeException::forType('object');

            expect($exception)->toBeInstanceOf(InvalidPatternTypeException::class);
            expect($exception->getMessage())->toBe('Expected string or array of strings for pattern, got object.');
        });

        test('message contains provided type', function (): void {
            $type = 'integer';
            $exception = InvalidPatternTypeException::forType($type);

            expect($exception->getMessage())->toContain($type);
        });

        test('message describes expected types', function (): void {
            $exception = InvalidPatternTypeException::forType('boolean');

            expect($exception->getMessage())->toContain('string');
            expect($exception->getMessage())->toContain('array');
        });

        test('implements GlobbyException interface', function (): void {
            $exception = InvalidPatternTypeException::forType('null');

            expect($exception)->toBeInstanceOf(GlobbyException::class);
        });

        test('extends InvalidArgumentException', function (): void {
            $exception = InvalidPatternTypeException::forType('null');

            expect($exception)->toBeInstanceOf(InvalidArgumentException::class);
        });
    });
});

describe('PathNotDirectoryException', function (): void {
    describe('Happy Path', function (): void {
        test('creates exception with forPath() factory method', function (): void {
            $exception = PathNotDirectoryException::forPath('/path/to/file.txt');

            expect($exception)->toBeInstanceOf(PathNotDirectoryException::class);
            expect($exception->getMessage())->toBe('Path is not a directory: /path/to/file.txt');
        });

        test('message contains provided path', function (): void {
            $path = '/home/user/document.pdf';
            $exception = PathNotDirectoryException::forPath($path);

            expect($exception->getMessage())->toContain($path);
        });

        test('message indicates path is not a directory', function (): void {
            $exception = PathNotDirectoryException::forPath('/file.txt');

            expect($exception->getMessage())->toContain('directory');
            expect($exception->getMessage())->toContain('not');
        });

        test('implements GlobbyException interface', function (): void {
            $exception = PathNotDirectoryException::forPath('/path');

            expect($exception)->toBeInstanceOf(GlobbyException::class);
        });

        test('extends RuntimeException', function (): void {
            $exception = PathNotDirectoryException::forPath('/path');

            expect($exception)->toBeInstanceOf(RuntimeException::class);
        });
    });
});
