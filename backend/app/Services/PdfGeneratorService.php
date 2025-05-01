<?php

namespace App\Services;

use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Exception;
use Storage;
use Str;

class PdfGeneratorService
{
	/**
	 * Function to apply needed css styles for A4 PDF format
	 */
	public function injectBasicCssIntoHtml(string $html) 
	{
		$css = <<<CSS
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12pt;
            line-height: 1.5;
        }

        p { margin: 0 0 12pt; }

        .tt-table       { width: 100%; border-collapse: collapse; margin: 12pt 0; }
        .tt-table th,
        .tt-table td    { border: 1px solid #d1d5db; padding: 6px 8px; }
        .tt-table th    { background: #f3f4f6; font-weight: 600; text-align: left; }
        .tt-table tbody tr:nth-child(even) { background: #fafafa; }

        .tt-token[data-token] { background: #fff7d6; }
        @media print { .tt-token[data-token] { background: transparent; } }
    CSS;

		return <<<HTML
            <!doctype html>
            <html lang="es">
            <head>
                <meta charset="utf-8">
                <style>{$css}</style>
            </head>
            <body>
                {$html}
            </body>
            </html>
        HTML;
	}

	public function generatePdfFromHtml(string $html, string $folder = 'contract-templates', ?string $name = null)
	{
		$html = $this->injectBasicCssIntoHtml($html);

		$slug      = $name ? Str::slug($name) . '-' : '';
		$fileName  = $slug . Str::uuid()->toString() . '.pdf';
		$filePath  = trim($folder, '/') . '/' . $fileName;

		try {
			$pdfBinary = PDF::loadHTML($html)
				->setPaper('a4')
				->setOrientation('portrait')
				->setOption('encoding', 'utf-8')
				->setOption('margin-top', 25)      
				->setOption('margin-bottom', 25)
				->setOption('margin-left', 20)
				->setOption('margin-right', 20) 
				->setOption('footer-center', '[page]')
				->output();

			Storage::disk('private')->put(path: $filePath, contents: $pdfBinary);
		} catch (Exception $e) {
			throw new Exception('PDF generation failed: ' . $e->getMessage(), 0, $e);
		}

		return $filePath;
	}
}
