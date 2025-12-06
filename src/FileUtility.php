<?php
	
	declare(strict_types=1);
	
	namespace Ilaion\FileUtilities;
	// Updated namespace to match composer.json PSR-4 mapping
	
	use RuntimeException;
	
	/**
	 * FileUtility - Main file operations utility class
	 */
	class FileUtility
	{
		protected string $basePath;
		protected array  $options;
		
		/**
		 * @param string $basePath Base directory for file operations
		 * @param array  $options  Configuration options
		 */
		public function __construct(string $basePath = '', array $options = [])
		{
			$this->basePath = $basePath ?: getcwd();
			$this->options  = array_merge(
				[
					'create_directories' => true,
					'overwrite'          => false,
				], $options
			);
		}
		
		/**
		 * Set a configuration option
		 *
		 * @param string $key   Option key
		 * @param mixed  $value Option value
		 *
		 * @return self
		 */
		public function setOption(string $key, mixed $value): self
		{
			$this->options[$key] = $value;
			
			return $this;
		}
		
		/**
		 * Get a configuration option
		 */
		public function getOption(string $key, mixed $default = null): mixed
		{
			return $this->options[$key] ?? $default;
		}
		
		/**
		 * Get the base path
		 */
		public function getBasePath(): string
		{
			return $this->basePath;
		}
		
		////////////////////////////////////////////////////////////////
		/// File Info
		////////////////////////////////////////////////////////////////
		
		/**
		 * Check if file exists
		 */
		public function exists(string $path): bool
		{
			return file_exists($this->resolvePath($path));
		}
		
		/**
		 * Get file size in bytes
		 *
		 * @throws RuntimeException If file doesn't exist
		 */
		public function size(string $path): int
		{
			$fullPath = $this->resolvePath($path);
			if (!file_exists($fullPath)) {
				throw new RuntimeException("File not found: {$fullPath}");
			}
			$size = filesize($fullPath);
			if ($size === false) {
				throw new RuntimeException("Failed to get file size: {$fullPath}");
			}
			
			return $size;
		}
		
		/**
		 * Resolve path relative to basePath
		 *
		 * @return string Resolved absolute path
		 */
		protected function resolvePath(string $path): string
		{
			// If absolute path, return as-is
			if ($this->isAbsolutePath($path)) {
				return $path;
			}
			
			// Otherwise, resolve relative to basePath
			return rtrim($this->basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
		}
		
		/**
		 * Check if path is absolute
		 */
		protected function isAbsolutePath(string $path): bool
		{
			// Unix-like systems
			if (str_starts_with($path, '/')) {
				return true;
			}
			// Windows systems
			if (substr($path, 1, 1) === ':') {
				return true;
			}
			
			return false;
		}
		
		////////////////////////////////////////////////////////////////
		/// File Operations
		////////////////////////////////////////////////////////////////
		
		/**
		 * Read file contents
		 *
		 * @param string $path File path (relative to basePath or absolute)
		 *
		 * @return string File contents
		 * @throws RuntimeException If file cannot be read
		 */
		public function read(string $path): string
		{
			$fullPath = $this->resolvePath($path);
			if (!file_exists($fullPath)) {
				throw new RuntimeException("File not found: {$fullPath}");
			}
			if (!is_readable($fullPath)) {
				throw new RuntimeException("File not readable: {$fullPath}");
			}
			$contents = file_get_contents($fullPath);
			if ($contents === false) {
				throw new RuntimeException("Failed to read file: {$fullPath}");
			}
			
			return $contents;
		}
		
		/**
		 * Write contents to file
		 *
		 * @param string $path     File path
		 * @param string $contents Contents to write
		 *
		 * @return bool Success status
		 * @throws RuntimeException If write fails
		 */
		public function write(string $path, string $contents): bool
		{
			$fullPath = $this->resolvePath($path);
			if (file_exists($fullPath) && !$this->options['overwrite']) {
				throw new RuntimeException("File already exists and overwrite is disabled: {$fullPath}");
			}
			$directory = dirname($fullPath);
			if (!is_dir($directory) && $this->options['create_directories']) {
				if (!mkdir($directory, 0755, true) && !is_dir($directory)) {
					throw new RuntimeException("Failed to create directory: {$directory}");
				}
			}
			$result = file_put_contents($fullPath, $contents);
			if ($result === false) {
				throw new RuntimeException("Failed to write file: {$fullPath}");
			}
			
			return true;
		}
		
		/**
		 * Delete a file
		 *
		 * @throws RuntimeException If deletion fails
		 */
		public function delete(string $path): bool
		{
			$fullPath = $this->resolvePath($path);
			if (!file_exists($fullPath)) {
				return true; // Already deleted
			}
			if (!unlink($fullPath)) {
				throw new RuntimeException("Failed to delete file: {$fullPath}");
			}
			
			return true;
		}
		
		/**
		 * Copy a file (single step)
		 *
		 * @param string $source      Source file path (relative or absolute)
		 * @param string $destination Destination file path
		 * @param bool   $createDest  Whether to create destination directories
		 */
		public function copy(string $source, string $destination, bool $createDest = false): bool
		{
			$sourcePath = $this->resolvePath($source);
			$destPath   = $this->resolvePath($destination);
			
			if (!file_exists($sourcePath)) {
				throw new RuntimeException("Source file not found: {$sourcePath}");
			}
			
			$destDir = dirname($destPath);
			if (!is_dir($destDir) && ($createDest || $this->options['create_directories'])) {
				$this->mkdir($destDir, 0755, true);
			}
			
			if (!@copy($sourcePath, $destPath)) {
				throw new RuntimeException("Failed to copy file from {$sourcePath} to {$destPath}");
			}
			
			return true;
		}
		
		/**
		 * Move a file – tries rename, falls back to copy + delete.
		 */
		public function move(string $source, string $destination, bool $createDest = false): bool
		{
			$sourcePath = $this->resolvePath($source);
			$destPath   = $this->resolvePath($destination);
			
			if (!file_exists($sourcePath)) {
				throw new RuntimeException("Source file not found: {$sourcePath}");
			}
			
			$destDir = dirname($destPath);
			if (!is_dir($destDir) && ($createDest || $this->options['create_directories'])) {
				$this->mkdir($destDir, 0755, true);
			}
			
			// Try atomic rename first
			if (@rename($sourcePath, $destPath)) {
				return true;
			}
			
			// Fallback: copy then delete
			$this->copy($sourcePath, $destPath, $createDest);
			$this->delete($sourcePath);
			
			return true;
		}
		
		/**
		 * Rename a file in place (simple wrapper around move).
		 */
		public function rename(string $source, string $newName): bool
		{
			$sourcePath = $this->resolvePath($source);
			$dir        = dirname($sourcePath);
			$destPath   = $dir . DIRECTORY_SEPARATOR . basename($newName);
			
			return $this->move($sourcePath, $destPath);
		}
		
		/**
		 * Ensure directory exists (idempotent).
		 */
		public function mkdir(string $path, ?int $mode = null, bool $recursive = true): bool
		{
			if (is_dir($path)) {
				return true;
			}
			
			$mode     = $mode ?? 0755;
			$oldUmask = umask(0);
			$result   = @mkdir($path, $mode, $recursive);
			umask($oldUmask);
			
			if (!$result && !is_dir($path)) {
				throw new RuntimeException("Failed to create directory: {$path}");
			}
			
			return true;
		}
	}
