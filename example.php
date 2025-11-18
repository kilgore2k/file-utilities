<?php

declare(strict_types=1);

// Example usage of FileUtilities

require_once __DIR__ . '/vendor/autoload.php';

use FileUtilities\FileUtility;

// Create instance with current directory as base
$fileUtil = new FileUtility(__DIR__);

echo "=== File Utilities Example ===\n\n";

// 1. Write a file
echo "1. Writing file...\n";
$fileUtil->write('examples/test.txt', "Hello from FileUtilities!\nCreated at: " . date('Y-m-d H:i:s'));
echo "   ✓ File written to examples/test.txt\n\n";

// 2. Read the file
echo "2. Reading file...\n";
$content = $fileUtil->read('examples/test.txt');
echo "   Content: {$content}\n\n";

// 3. Check if exists
echo "3. Checking if file exists...\n";
$exists = $fileUtil->exists('examples/test.txt');
echo "   Exists: " . ($exists ? 'Yes' : 'No') . "\n\n";

// 4. Get file size
echo "4. Getting file size...\n";
$size = $fileUtil->size('examples/test.txt');
echo "   Size: {$size} bytes\n\n";

// 5. Copy the file
echo "5. Copying file...\n";
$fileUtil->copy('examples/test.txt', 'examples/backup.txt');
echo "   ✓ Copied to examples/backup.txt\n\n";

// 6. Working with options
echo "6. Testing overwrite protection...\n";
$protectedUtil = new FileUtility(__DIR__, ['overwrite' => false]);
try {
    $protectedUtil->write('examples/test.txt', 'This should fail');
    echo "   ✗ Unexpected success\n";
} catch (\RuntimeException $e) {
    echo "   ✓ Overwrite prevented (as expected)\n";
}
echo "\n";

// 7. Clean up
echo "7. Cleaning up...\n";
$fileUtil->delete('examples/test.txt');
$fileUtil->delete('examples/backup.txt');
@rmdir(__DIR__ . '/examples');
echo "   ✓ Files deleted\n\n";

echo "=== Example Complete ===\n";
