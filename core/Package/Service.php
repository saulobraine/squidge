<?php

/**
 * Service
 *
 * Service os responsible for processing file attachments
 * and executing commands.
 *
 * @package     Squidge
 * @version     0.1.4
 * @author      Ainsley Clark
 * @category    Class
 * @repo        https://github.com/ainsleyclark/squidge
 *
 */

namespace Squidge\Package;

use Exception;
use Squidge\Log\Logger;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Service
{

	/**
	 * META_KEY is the meta key for lookup for the
	 * service.
	 */
	const META_KEY = "_squidge_compressed";

	/**
	 * Processes the file attachment.
	 *
	 * @param $attachment
	 * @param $args
	 * @return void
	 * @throws Exception
	 * @since 0.1.0
	 * @date 24/11/2021
	 */
	public static function process($attachment, $args)
	{

		// If the attachment is an ID, obtain the metadata.
		if (is_int($attachment)) {
			$attachment = wp_get_attachment_metadata($attachment);
		}
		// Return if the library is not installed.
		if (!self::installed()) {
			return;
		}

		// Check if the file key exists.
		if (!isset($attachment['file'])) {
			throw new Exception("File attachment is not set.");
		}

		// Obtain the file and check if it exists.
		$mainFile = self::get_file_path($attachment['file']);
		if (!$mainFile) {
			return;
		}

		// Check if the attachment has already been compressed.
		$id = attachment_url_to_postid($attachment['file']);
		if (self::has_compressed($id) && !$args['force']) {
			return;
		}

		// Convert main image.
		if (!$args['thumbnailsOnly']) static::convert($mainFile, self::get_mime_type($mainFile), $args);

		// Loop over the sizes and convert them.
		foreach ($attachment['sizes'] as $size) {
			if (!isset($size['file'])) {
				continue;
			}
			$basepath = str_replace(basename($attachment['file']), "", $attachment['file']);
			$path = self::get_file_path($basepath . $size['file']);
			if (!$path) {
				continue;
			}
			static::convert($path, self::get_mime_type($path), $args);
		}

		// Update post meta for attachment.
		self::update_meta($id);
	}

	/**
	 * Deletes all images  with file extensions associated
	 * with the image (if the file exists).
	 *
	 * @param $id
	 * @return void
	 * @since 0.1.0
	 * @date 24/11/2021
	 */
	public static function delete($id)
	{
		// Delete the original file.
		$original = wp_get_original_image_path($id) . static::extension();
		if (file_exists($original)) {
			unlink($original);
		}

		// Delete the image sizes.
		$sizes = get_intermediate_image_sizes();
		foreach ($sizes as $size) {
			$fileInfos = image_get_intermediate_size($id, $size);
			if (empty($fileInfos)) {
				continue;
			}
			$path = self::get_file_path($fileInfos['path'] . static::extension());
			if (!$path) {
				continue;
			}
			unlink($path);
		}
	}

	/**
	 * Checks if a command exists.
	 *
	 * Uses native PHP methods to check if the library
	 * is installed on the client's operating system.
	 * Checks to see if the command name is in the allowed
	 * array before continuing.
	 *
	 * @return bool
	 * @since 0.1.2
	 * @date 05/12/2021
	 */
	public static function installed()
	{
		$allowed = [
			'jpegoptim',
			'optipng',
			'cwebp',
			'avifenc',
		];
		if (!in_array(static::cmd_name(), $allowed)) {
			return false;
		}

		// Use native PHP methods for cross-platform compatibility
		$command = static::cmd_name();

		// Method 1: Check if command exists in PATH using exec with error suppression
		if (function_exists('exec')) {
			$output = [];
			$return_var = 0;

			// Try to execute the command with --version or --help to check if it exists
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
				// Windows: try to execute the command directly
				@exec($command . ' --version 2>nul', $output, $return_var);
				if ($return_var === 0) {
					return true;
				}

				// Fallback: check common Windows paths
				$paths = [
					'C:\\ProgramData\\chocolatey\\bin\\',
					'C:\\Users\\' . get_current_user() . '\\scoop\\shims\\',
					'C:\\tools\\',
					'C:\\Program Files\\',
					'C:\\Program Files (x86)\\'
				];

				foreach ($paths as $path) {
					if (file_exists($path . $command . '.exe')) {
						return true;
					}
				}
			} else {
				// Unix/Linux: use which command
				@exec("which $command 2>/dev/null", $output, $return_var);
				if ($return_var === 0) {
					return true;
				}
			}
		}

		// Method 2: Check if the command is accessible via shell_exec
		if (function_exists('shell_exec')) {
			$output = @shell_exec($command . ' --version 2>&1');
			if (!empty($output) && strpos($output, 'error') === false) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Obtains the absolute filepath including the
	 * upload directory.
	 * If the file does not exist on the file system, the
	 * function will return false.
	 *
	 * @param $path - Path of file inside uploads folder
	 * @return string
	 * @since 0.1.0
	 * @date 24/11/2021
	 */
	private static function get_file_path($path)
	{
		$file = wp_get_upload_dir()['basedir'] . DIRECTORY_SEPARATOR . $path;
		if (file_exists($file)) {
			return $file;
		}
		return false;
	}

	/**
	 * Returns the mimetype of a file passed.
	 *
	 * @param $file
	 * @return false|string
	 * @since 0.1.0
	 * @date 24/11/2021
	 */
	private static function get_mime_type($file)
	{
		return mime_content_type($file);
	}

	/**
	 * Determines if the attachment has been compressed.
	 *
	 * @param $id
	 * @return bool
	 * @since 0.1.4
	 * @date 21/12/2021
	 */
	public static function has_compressed($id)
	{
		$meta = get_post_meta($id, self::META_KEY . '_' . static::cmd_name());
		return !empty($meta);
	}

	/**
	 * Updates post meta for squidge.
	 *
	 * @param $id
	 * @since 0.1.4
	 * @date 21/12/2021
	 */
	public static function update_meta($id)
	{
		update_post_meta($id, self::META_KEY . '_' . static::cmd_name(), true);
	}
}
