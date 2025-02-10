<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Exception;

class UploadFilesService
{
	public function __construct() {}

	/**
	 * Generic method to store a base64 file on a specified folder in the 'private' disk
	 *
	 * @param string $base64Data
	 * @param string $folder
	 * @param string $extension
	 * @return string
	 */
	public function storeFile(string $base64Data, string $folder = 'files/', string $extension = 'png'): string
	{
		$decodedFile = base64_decode($base64Data);
		$filename = uniqid('file_') . '.' . $extension;
		$filePath = rtrim($folder, '/') . '/' . $filename;
		Storage::disk('private')->put($filePath, $decodedFile);
		return $filePath;
	}

	/**
	 * Store profile image using the preset folder.
	 *
	 * @param string $base64Image
	 * @return string
	 */
	public function storeProfileImage(string $base64Image): string
	{
		return $this->storeFile($base64Image, 'images/profile_pictures', 'png');
	}

	/**
	 * Get file content and MIME type from the 'private' disk.
	 *
	 * @param string $path
	 * @return array
	 * @throws \Exception if the file is not found
	 */
	public function getFile(string $path): array
	{
		$disk = Storage::disk('private');

		if (!$disk->exists($path)) {
			throw new Exception("File not found at: {$path}");
		}

		$content = $disk->get($path);
		$fullPath = $disk->path($path);
		$mime = mime_content_type($fullPath);

		return [
			'content' => $content,
			'mime' => $mime,
		];
	}
}
