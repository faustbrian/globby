---
title: Filtering Options
description: Filter glob results by various criteria.
---

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
