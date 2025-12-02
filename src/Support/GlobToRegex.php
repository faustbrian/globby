<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Globby\Support;

use function mb_strlen;
use function mb_substr;
use function preg_match;
use function preg_quote;

/**
 * Converts glob patterns to regular expressions.
 *
 * Handles complex glob patterns including:
 * - Wildcards: * (any chars), ? (single char), ** (recursive)
 * - Character classes: [abc], [a-z], [!abc], [^abc]
 * - POSIX classes: [[:alpha:]], [[:digit:]], etc.
 * - Escaped characters: \[, \], \*, \?
 * - Brace expansion: {a,b,c}
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
final readonly class GlobToRegex
{
    /**
     * POSIX character class mappings.
     *
     * @var array<string, string>
     */
    private const array POSIX_CLASSES = [
        '[:alnum:]' => '[a-zA-Z0-9]',
        '[:alpha:]' => '[a-zA-Z]',
        '[:ascii:]' => '[\x00-\x7F]',
        '[:blank:]' => '[ \t]',
        '[:cntrl:]' => '[\x00-\x1F\x7F]',
        '[:digit:]' => '[0-9]',
        '[:graph:]' => '[\x21-\x7E]',
        '[:lower:]' => '[a-z]',
        '[:print:]' => '[\x20-\x7E]',
        '[:punct:]' => '[!"#$%&\'()*+,\-./:;<=>?@[\\\]^_`{|}~]',
        '[:space:]' => '[ \t\n\r\f\v]',
        '[:upper:]' => '[A-Z]',
        '[:word:]' => '[a-zA-Z0-9_]',
        '[:xdigit:]' => '[0-9A-Fa-f]',
    ];

    /**
     * Convert a glob pattern to a regular expression.
     *
     * @param  string $pattern   The glob pattern
     * @param  string $delimiter The regex delimiter (default: #)
     * @return string The regular expression
     */
    public function convert(string $pattern, string $delimiter = '#'): string
    {
        $regex = '';
        $length = mb_strlen($pattern);
        $i = 0;
        $inGroup = false;

        while ($i < $length) {
            $char = mb_substr($pattern, $i, 1);
            $next = $i + 1 < $length ? mb_substr($pattern, $i + 1, 1) : '';

            // Handle escape sequences
            if ($char === '\\' && $next !== '') {
                $regex .= preg_quote($next, $delimiter);
                $i += 2;

                continue;
            }

            // Inside character class
            if ($inGroup) {
                if ($char === ']') {
                    $regex .= ']';
                    $inGroup = false;
                    ++$i;

                    continue;
                }

                // Check for POSIX character class
                $posixMatch = $this->matchPosixClass($pattern, $i);

                if ($posixMatch !== null) {
                    $regex .= $posixMatch['regex'];
                    $i += $posixMatch['length'];

                    continue;
                }

                // Check for character range (e.g., a-z)
                if ($next === '-' && $i + 2 < $length) {
                    $rangeEnd = mb_substr($pattern, $i + 2, 1);

                    if ($rangeEnd !== ']') {
                        $regex .= preg_quote($char, $delimiter).'-'.preg_quote($rangeEnd, $delimiter);
                        $i += 3;

                        continue;
                    }
                }

                // Regular character in group
                $regex .= preg_quote($char, $delimiter);
                ++$i;

                continue;
            }

            // Outside character class
            switch ($char) {
                case '*':
                    // Check for globstar (**)
                    if ($next === '*') {
                        $regex .= '.*';
                        $i += 2;
                    } else {
                        // Single * doesn't match directory separators
                        $regex .= '[^/]*';
                        ++$i;
                    }

                    break;

                case '?':
                    // Single character (not directory separator)
                    $regex .= '[^/]';
                    ++$i;

                    break;

                case '[':
                    // Start character class
                    $inGroup = true;

                    // Check for negation
                    if ($next === '!' || $next === '^') {
                        $regex .= '[^';
                        $i += 2;

                        // Handle ] as first char after negation
                        if ($i < $length && mb_substr($pattern, $i, 1) === ']') {
                            $regex .= '\\]';
                            ++$i;
                        }
                    } else {
                        $regex .= '[';
                        ++$i;

                        // Handle ] as first char in group
                        if ($i < $length && mb_substr($pattern, $i, 1) === ']') {
                            $regex .= '\\]';
                            ++$i;
                        }
                    }

                    break;

                case '{':
                    // Start brace expansion (convert to alternation)
                    $regex .= '(?:';
                    ++$i;

                    break;

                case '}':
                    // End brace expansion
                    $regex .= ')';
                    ++$i;

                    break;

                case ',':
                    // Separator in brace expansion
                    $regex .= '|';
                    ++$i;

                    break;

                default:
                    // Escape regex special characters
                    $regex .= preg_quote($char, $delimiter);
                    ++$i;

                    break;
            }
        }

        return $delimiter.'^'.$regex.'$'.$delimiter.'u';
    }

    /**
     * Check if a value matches a glob pattern.
     *
     * @param  string $pattern The glob pattern
     * @param  string $value   The value to match
     * @return bool   True if the value matches
     */
    public function match(string $pattern, string $value): bool
    {
        $regex = $this->convert($pattern);

        return preg_match($regex, $value) === 1;
    }

    /**
     * Match a POSIX character class at the given position.
     *
     * @param  string                                 $pattern The pattern
     * @param  int                                    $pos     Current position
     * @return null|array{regex: string, length: int} Match info or null
     */
    private function matchPosixClass(string $pattern, int $pos): ?array
    {
        foreach (self::POSIX_CLASSES as $posix => $regex) {
            $length = mb_strlen($posix);

            if (mb_substr($pattern, $pos, $length) === $posix) {
                // Remove outer brackets as we're already in a character class
                $innerRegex = mb_substr($regex, 1, -1);

                return ['regex' => $innerRegex, 'length' => $length];
            }
        }

        return null;
    }
}
