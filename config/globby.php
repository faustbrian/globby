<?php

declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Default Options
    |--------------------------------------------------------------------------
    |
    | These are the default options used when calling Globby::glob() without
    | explicitly providing options. You can override these on a per-call basis
    | by passing an options array or GlobbyOptions instance.
    |
    */

    'defaults' => [
        /*
        |--------------------------------------------------------------------------
        | Expand Directories
        |--------------------------------------------------------------------------
        |
        | When set to true, if a pattern matches a directory, it will be
        | automatically expanded to match all files within that directory.
        | Set to false to disable, or provide an array with 'files' and/or
        | 'extensions' keys to limit what gets matched:
        |
        | 'expandDirectories' => [
        |     'files' => ['*.php', '*.js'],
        |     'extensions' => ['php', 'js'],
        | ]
        |
        */

        'expandDirectories' => true,

        /*
        |--------------------------------------------------------------------------
        | Gitignore Support
        |--------------------------------------------------------------------------
        |
        | When enabled, Globby will respect .gitignore files when matching.
        | It searches for .gitignore files from the cwd downward and, if in
        | a git repository, also respects parent .gitignore files up to the
        | repository root.
        |
        */

        'gitignore' => false,

        /*
        |--------------------------------------------------------------------------
        | Only Files
        |--------------------------------------------------------------------------
        |
        | When true, only files are returned in results. Set to false to
        | include directories in results as well.
        |
        */

        'onlyFiles' => true,

        /*
        |--------------------------------------------------------------------------
        | Match Dotfiles
        |--------------------------------------------------------------------------
        |
        | By default, files starting with a dot (.) are not matched. Set this
        | to true to include dotfiles in results.
        |
        */

        'dot' => false,

        /*
        |--------------------------------------------------------------------------
        | Follow Symbolic Links
        |--------------------------------------------------------------------------
        |
        | When true, symbolic links are followed during directory traversal.
        | Set to false to skip symbolic links.
        |
        */

        'followSymbolicLinks' => true,

        /*
        |--------------------------------------------------------------------------
        | Suppress Errors
        |--------------------------------------------------------------------------
        |
        | When true, errors encountered while reading files or directories
        | (such as permission errors) are silently suppressed. When false,
        | these errors are thrown.
        |
        */

        'suppressErrors' => false,

        /*
        |--------------------------------------------------------------------------
        | Absolute Paths
        |--------------------------------------------------------------------------
        |
        | When true, absolute paths are returned. When false (default),
        | paths are relative to the cwd.
        |
        */

        'absolute' => false,

        /*
        |--------------------------------------------------------------------------
        | Unique Results
        |--------------------------------------------------------------------------
        |
        | When true (default), duplicate paths are removed from results.
        | Set to false to allow duplicates when using multiple patterns
        | that may match the same files.
        |
        */

        'unique' => true,
    ],

];
