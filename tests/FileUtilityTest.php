<?php

declare(strict_types=1);

namespace Ilaion\FileUtilities\Tests;

use Ilaion\FileUtilities\FileUtility;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class FileUtilityTest extends TestCase
{
    private string $testDir;
    private FileUtility $fileUtility;

    protected function setUp(): void
    {
        $this->testDir = sys_get_temp_dir() . '/file-utilities-test-' . uniqid();
        mkdir($this->testDir, 0755, true);
        $this->fileUtility = new FileUtility($this->testDir);
    }

    protected function tearDown(): void
    {
        // Clean up test directory
        if (is_dir($this->testDir)) {
            $this->removeDirectory($this->testDir);
        }
    }

    public function testWriteAndRead(): void
    {
        $content = 'Hello, World!';
        $this->fileUtility->write('test.txt', $content);

        $this->assertTrue($this->fileUtility->exists('test.txt'));
        $this->assertEquals($content, $this->fileUtility->read('test.txt'));
    }

    public function testReadNonExistentFileThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File not found');
        
        $this->fileUtility->read('non-existent.txt');
    }

    public function testWriteCreatesDirectories(): void
    {
        $this->fileUtility->write('nested/dir/test.txt', 'content');
        
        $this->assertTrue($this->fileUtility->exists('nested/dir/test.txt'));
    }

    public function testDelete(): void
    {
        $this->fileUtility->write('delete-me.txt', 'content');
        $this->assertTrue($this->fileUtility->exists('delete-me.txt'));
        
        $this->fileUtility->delete('delete-me.txt');
        $this->assertFalse($this->fileUtility->exists('delete-me.txt'));
    }

    public function testCopy(): void
    {
        $content = 'Test content';
        $this->fileUtility->write('source.txt', $content);
        
        $this->fileUtility->copy('source.txt', 'destination.txt');
        
        $this->assertTrue($this->fileUtility->exists('destination.txt'));
        $this->assertEquals($content, $this->fileUtility->read('destination.txt'));
    }

    public function testSize(): void
    {
        $content = 'Hello';
        $this->fileUtility->write('size-test.txt', $content);
        
        $this->assertEquals(strlen($content), $this->fileUtility->size('size-test.txt'));
    }

    public function testOptions(): void
    {
        $fileUtility = new FileUtility($this->testDir, [
            'overwrite' => false,
            'create_directories' => true,
        ]);

        $fileUtility->write('test.txt', 'content');
        
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('already exists');
        
        $fileUtility->write('test.txt', 'new content');
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }
}
