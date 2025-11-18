# File Utilities

A simple PHP library for common file operations with a clean, object-oriented API.

## Package Name

This library is published (locally) under the Composer package name: `ilaion/file-utilities`.

## Installation

```bash
composer require ilaion/file-utilities
```

If you are developing locally and haven't published the package, add the path repository in your root project's `composer.json`:

```json
{
  "repositories": [
    { "type": "path", "url": "relative/path/to/file-utilities" }
  ]
}
```

Then require it:

```bash
composer require ilaion/file-utilities:*@dev
```

## Features

- Read file contents with error handling
- Write files with optional directory auto-creation and overwrite protection
- Copy, delete, and size operations
- Configurable base path per instance
- Options API (`overwrite`, `create_directories`)
- PSR-4 autoload ready
- Fully tested with PHPUnit
- Strict types for safer APIs

## Directory Structure

```
file-utilities/
├── composer.json
├── src/
│   └── FileUtility.php
├── tests/
│   └── FileUtilityTest.php
├── example.php
└── README.md
```

## Basic Usage (Instance-based - Recommended)

```php
<?php
use FileUtilities\FileUtility;

// Create instance with base directory
$fileUtil = new FileUtility('/path/to/base/directory');

// Write a file
$fileUtil->write('example.txt', 'Hello, World!');

// Read a file
$content = $fileUtil->read('example.txt');

// Check if file exists
if ($fileUtil->exists('example.txt')) {
    echo "File exists!";
}

// Copy a file
$fileUtil->copy('example.txt', 'backup/example.txt');

// Get file size
$size = $fileUtil->size('example.txt');

// Delete a file
$fileUtil->delete('example.txt');
```

## Running the Included Example

The repository includes a runnable demonstration script: `example.php`.

```bash
php example.php
```

Output will walk through write, read, exists, size, copy, overwrite protection, and cleanup.

## Configuration Options

```php
$fileUtil = new FileUtility('/base/path', [
    'create_directories' => true,  // Auto-create directories when writing
    'overwrite' => false,          // Prevent overwriting existing files
]);

// Or set options after instantiation
$fileUtil->setOption('overwrite', true);
```

## Working with Absolute Paths

```php
$fileUtil = new FileUtility(); // defaults to getcwd()

// Absolute paths work directly
$fileUtil->write('/tmp/test.txt', 'content');
$content = $fileUtil->read('/tmp/test.txt');
```

## Why Instance-based over Static?

**Benefits:**
- Configurable per instance (different base paths, options)
- Easy to mock in tests
- Supports dependency injection
- Can maintain state when needed
- More flexible and extensible

**Example - Multiple instances:**
```php
$sourceUtil = new FileUtility('/source/directory');
$destUtil = new FileUtility('/destination/directory', [
    'create_directories' => true
]);

// Copy from source to destination
$content = $sourceUtil->read('file.txt');
$destUtil->write('file.txt', $content);
```

## Strict Types (`declare(strict_types=1);`)

All source files use strict types for safer APIs. This prevents silent type coercion. If a method expects `int` and you pass a string, PHP will throw a `TypeError` instead of silently converting it.

Benefits:
- Catches bugs early
- Makes intent explicit
- Improves static analysis and IDE hints
- Prevents subtle data issues

## Testing

Install dev dependencies then run PHPUnit:

```bash
composer install
vendor/bin/phpunit
```

## Extending

Potential additions you can implement:
- Recursive directory copy utilities
- File streaming / chunked reading
- Temporary file helpers
- Safe atomic writes (write + rename)

## Requirements

- PHP >= 8.1
- ext-json (optional for future enhancements)

## Contributing

1. Fork & clone
2. Create a feature branch
3. Run tests (`vendor/bin/phpunit`)
4. Submit a PR

## License

MIT
