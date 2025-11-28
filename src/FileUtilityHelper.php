<?php
	/**
	 * FileUtilityHelper.php
	 *
	 * @description : static interface to FileUtility
	 * @name        : FileUtilityHelper
	 * @id          : 20251118.220556.1
	 * @author      : rbarel
	 */
	
	namespace Ilaion\FileUtilities;
	
	class FileUtilityHelper
	{
		/**
		 * Build a normalized path from segments.
		 */
		public static function buildPath(array $segments): string
		{
			$filtered = [];
			foreach ($segments as $index => $segment) {
				if ($index === 0) {
					$segment = rtrim((string)$segment, DIRECTORY_SEPARATOR);
				}
				else {
					$segment = trim((string)$segment, DIRECTORY_SEPARATOR);
				}
				if ($segment !== '') {
					$filtered[] = $segment;
				}
			}
			return implode(DIRECTORY_SEPARATOR, $filtered);
		}
	}