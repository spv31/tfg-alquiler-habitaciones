<?php

namespace App\Services;

use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Exception;
use Storage;
use Str;

class PdfGeneratorService
{
	public function generatePdfFromHtml(string $html, string $folder = 'contract-templates', ?string $name = null)
	{
		$slug      = $name ? Str::slug($name) . '-' : '';
		$fileName  = $slug . Str::uuid()->toString() . '.pdf';
		$filePath  = trim($folder, '/') . '/' . $fileName;

		try {
			$pdfBinary = PDF::loadHTML($html)
				->setPaper('A4')
				->setOrientation('portrait')
				->setOption('encoding', 'utf-8')
				->output();
				
			Storage::disk('private')->put(path: $filePath, contents: $pdfBinary);
			
			} catch (Exception $e) {
			throw new Exception('PDF generation failed: ' . $e->getMessage(), 0, $e);
		}

		return $filePath;
	}
}
