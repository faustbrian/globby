<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Globby\Exceptions;

use Throwable;

/**
 * Base exception interface for all Globby-specific exceptions.
 *
 * This marker interface allows consumers to catch all Globby exceptions with a
 * single catch block, providing a convenient way to handle all errors originating
 * from the Globby library. All concrete exception classes in the library implement
 * this interface to enable unified exception handling.
 *
 * ```php
 * try {
 *     $results = $globby->find('*.php');
 * } catch (GlobbyException $e) {
 *     // Handle any Globby-specific error
 * }
 * ```
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface GlobbyException extends Throwable {}
