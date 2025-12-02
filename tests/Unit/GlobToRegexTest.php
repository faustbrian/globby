<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Globby\Support\GlobToRegex;

describe('GlobToRegex', function (): void {
    beforeEach(function (): void {
        $this->converter = new GlobToRegex();
    });

    describe('Basic Wildcards', function (): void {
        test('converts * to match any characters except slash', function (): void {
            expect($this->converter->match('*.txt', 'file.txt'))->toBeTrue();
            expect($this->converter->match('*.txt', 'document.txt'))->toBeTrue();
            expect($this->converter->match('*.txt', 'file.md'))->toBeFalse();
            expect($this->converter->match('*.txt', 'path/file.txt'))->toBeFalse();
        });

        test('converts ** to match any characters including slash', function (): void {
            expect($this->converter->match('**/*.txt', 'file.txt'))->toBeFalse();
            expect($this->converter->match('**/*.txt', 'path/file.txt'))->toBeTrue();
            expect($this->converter->match('**/*.txt', 'deep/path/file.txt'))->toBeTrue();
        });

        test('converts ? to match single character', function (): void {
            expect($this->converter->match('file?.txt', 'file1.txt'))->toBeTrue();
            expect($this->converter->match('file?.txt', 'fileA.txt'))->toBeTrue();
            expect($this->converter->match('file?.txt', 'file.txt'))->toBeFalse();
            expect($this->converter->match('file?.txt', 'file12.txt'))->toBeFalse();
        });
    });

    describe('Character Classes', function (): void {
        test('matches character class [abc]', function (): void {
            expect($this->converter->match('file[123].txt', 'file1.txt'))->toBeTrue();
            expect($this->converter->match('file[123].txt', 'file2.txt'))->toBeTrue();
            expect($this->converter->match('file[123].txt', 'file4.txt'))->toBeFalse();
        });

        test('matches character range [a-z]', function (): void {
            expect($this->converter->match('file[a-z].txt', 'filea.txt'))->toBeTrue();
            expect($this->converter->match('file[a-z].txt', 'filez.txt'))->toBeTrue();
            expect($this->converter->match('file[a-z].txt', 'file1.txt'))->toBeFalse();
        });

        test('matches negated character class with exclamation [!abc]', function (): void {
            expect($this->converter->match('file[!123].txt', 'file4.txt'))->toBeTrue();
            expect($this->converter->match('file[!123].txt', 'filea.txt'))->toBeTrue();
            expect($this->converter->match('file[!123].txt', 'file1.txt'))->toBeFalse();
        });

        test('matches negated character class with caret [^abc]', function (): void {
            expect($this->converter->match('file[^123].txt', 'file4.txt'))->toBeTrue();
            expect($this->converter->match('file[^123].txt', 'file1.txt'))->toBeFalse();
        });

        test('matches combined character range [0-9A-Za-z]', function (): void {
            expect($this->converter->match('id[0-9A-Za-z].txt', 'id5.txt'))->toBeTrue();
            expect($this->converter->match('id[0-9A-Za-z].txt', 'idX.txt'))->toBeTrue();
            expect($this->converter->match('id[0-9A-Za-z].txt', 'ida.txt'))->toBeTrue();
            expect($this->converter->match('id[0-9A-Za-z].txt', 'id-.txt'))->toBeFalse();
        });
    });

    describe('POSIX Character Classes', function (): void {
        test('matches [:digit:]', function (): void {
            expect($this->converter->match('data[[:digit:]].log', 'data5.log'))->toBeTrue();
            expect($this->converter->match('data[[:digit:]].log', 'data0.log'))->toBeTrue();
            expect($this->converter->match('data[[:digit:]].log', 'datax.log'))->toBeFalse();
        });

        test('matches [:alpha:]', function (): void {
            expect($this->converter->match('file[[:alpha:]].txt', 'filea.txt'))->toBeTrue();
            expect($this->converter->match('file[[:alpha:]].txt', 'fileZ.txt'))->toBeTrue();
            expect($this->converter->match('file[[:alpha:]].txt', 'file1.txt'))->toBeFalse();
        });

        test('matches [:alnum:]', function (): void {
            expect($this->converter->match('id[[:alnum:]].txt', 'id5.txt'))->toBeTrue();
            expect($this->converter->match('id[[:alnum:]].txt', 'ida.txt'))->toBeTrue();
            expect($this->converter->match('id[[:alnum:]].txt', 'id-.txt'))->toBeFalse();
        });

        test('matches [:lower:]', function (): void {
            expect($this->converter->match('var[[:lower:]].txt', 'vara.txt'))->toBeTrue();
            expect($this->converter->match('var[[:lower:]].txt', 'varA.txt'))->toBeFalse();
        });

        test('matches [:upper:]', function (): void {
            expect($this->converter->match('const[[:upper:]].txt', 'constA.txt'))->toBeTrue();
            expect($this->converter->match('const[[:upper:]].txt', 'consta.txt'))->toBeFalse();
        });

        test('matches [:xdigit:]', function (): void {
            expect($this->converter->match('hex[[:xdigit:]].txt', 'hexA.txt'))->toBeTrue();
            expect($this->converter->match('hex[[:xdigit:]].txt', 'hex9.txt'))->toBeTrue();
            expect($this->converter->match('hex[[:xdigit:]].txt', 'hexG.txt'))->toBeFalse();
        });
    });

    describe('Escaped Characters', function (): void {
        test('matches escaped brackets', function (): void {
            expect($this->converter->match('file\\[1\\].txt', 'file[1].txt'))->toBeTrue();
            expect($this->converter->match('file\\[1\\].txt', 'file1.txt'))->toBeFalse();
        });

        test('matches escaped asterisk', function (): void {
            expect($this->converter->match('file\\*.txt', 'file*.txt'))->toBeTrue();
            expect($this->converter->match('file\\*.txt', 'fileABC.txt'))->toBeFalse();
        });

        test('matches escaped question mark', function (): void {
            expect($this->converter->match('what\\?.txt', 'what?.txt'))->toBeTrue();
            expect($this->converter->match('what\\?.txt', 'whatX.txt'))->toBeFalse();
        });

        test('matches escaped backslash', function (): void {
            expect($this->converter->match('path\\\\file.txt', 'path\\file.txt'))->toBeTrue();
        });
    });

    describe('Brace Expansion', function (): void {
        test('matches brace alternatives', function (): void {
            expect($this->converter->match('file.{js,ts}', 'file.js'))->toBeTrue();
            expect($this->converter->match('file.{js,ts}', 'file.ts'))->toBeTrue();
            expect($this->converter->match('file.{js,ts}', 'file.py'))->toBeFalse();
        });

        test('matches multiple brace alternatives', function (): void {
            expect($this->converter->match('{src,lib}/*.{js,ts}', 'src/app.js'))->toBeTrue();
            expect($this->converter->match('{src,lib}/*.{js,ts}', 'lib/util.ts'))->toBeTrue();
            expect($this->converter->match('{src,lib}/*.{js,ts}', 'test/app.js'))->toBeFalse();
        });
    });

    describe('Complex Patterns', function (): void {
        test('matches complex pattern with escaped brackets and wildcards', function (): void {
            // Pattern: wow\[such\]?pat\*ter[nr][!,]*wild[[:digit:]]
            // Matches: wow[such] + single char + pat*ter + (n or r) + (not comma) + anything + wild + digit
            $pattern = 'wow\\[such\\]?pat\\*ter[nr][!,]*wild[[:digit:]]';

            // Both files have 'n' after 'ter' which matches [nr]
            expect($this->converter->match($pattern, 'wow[such]xpat*ternr7wild5'))->toBeTrue();
            expect($this->converter->match($pattern, 'wow[such]xpat*terns,wild9'))->toBeTrue();

            // This would NOT match because 'z' is not in [nr]
            expect($this->converter->match($pattern, 'wow[such]xpat*terz5wild3'))->toBeFalse();

            // Exact example from nick-jones/globby README
            expect($this->converter->match($pattern, 'wow[such]:pat*tern.!much.wild9'))->toBeTrue();
        });

        test('matches pattern with multiple character classes', function (): void {
            $pattern = 'log-[[:digit:]][[:digit:]][[:digit:]][[:digit:]]-[[:alpha:]][[:alpha:]][[:alpha:]].txt';

            expect($this->converter->match($pattern, 'log-2024-Jan.txt'))->toBeTrue();
            expect($this->converter->match($pattern, 'log-1999-Dec.txt'))->toBeTrue();
            expect($this->converter->match($pattern, 'log-24-Jan.txt'))->toBeFalse();
        });

        test('matches pattern combining all features', function (): void {
            $pattern = '{src,lib}/**/[[:alpha:]]*.{ts,js}';

            expect($this->converter->match($pattern, 'src/components/Button.ts'))->toBeTrue();
            expect($this->converter->match($pattern, 'lib/utils/helper.js'))->toBeTrue();
            expect($this->converter->match($pattern, 'src/123invalid.ts'))->toBeFalse();
        });
    });

    describe('Edge Cases', function (): void {
        test('handles empty pattern', function (): void {
            expect($this->converter->match('', ''))->toBeTrue();
            expect($this->converter->match('', 'anything'))->toBeFalse();
        });

        test('handles literal string without wildcards', function (): void {
            expect($this->converter->match('exact.txt', 'exact.txt'))->toBeTrue();
            expect($this->converter->match('exact.txt', 'other.txt'))->toBeFalse();
        });

        test('handles ] as first character in group', function (): void {
            // When ] is first in group, it's treated as literal
            expect($this->converter->match('file[]ab].txt', 'file].txt'))->toBeTrue();
            expect($this->converter->match('file[]ab].txt', 'filea.txt'))->toBeTrue();
            expect($this->converter->match('file[]ab].txt', 'filec.txt'))->toBeFalse();
        });

        test('handles special regex characters in pattern', function (): void {
            expect($this->converter->match('file.txt', 'file.txt'))->toBeTrue();
            expect($this->converter->match('file.txt', 'fileXtxt'))->toBeFalse();
            expect($this->converter->match('file(1).txt', 'file(1).txt'))->toBeTrue();
            expect($this->converter->match('price$100.txt', 'price$100.txt'))->toBeTrue();
        });
    });

    describe('Regex Generation', function (): void {
        test('generates valid regex with custom delimiter', function (): void {
            $regex = $this->converter->convert('*.txt', '/');

            expect($regex)->toStartWith('/^');
            expect($regex)->toEndWith('$/u');
        });

        test('escapes delimiter in pattern', function (): void {
            // # in pattern should be escaped when # is delimiter
            $regex = $this->converter->convert('file#1.txt', '#');

            expect(preg_match($regex, 'file#1.txt'))->toBe(1);
        });
    });
});
