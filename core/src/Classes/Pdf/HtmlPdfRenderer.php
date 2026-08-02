<?php

namespace Classes\Pdf;

use Classes\BaseService;

/**
 * Renders an HTML string to a PDF file using the native mPDF library (pure PHP —
 * no external wkhtmltopdf/WK_HTML_PATH process). Used for payslips so the visual
 * template design (HTML + CSS) is faithfully preserved in the PDF.
 *
 * mPDF 8.1.x is pinned (composer platform php 7.3.0) so it stays PHP 7.3
 * compatible.
 */
class HtmlPdfRenderer
{
    /**
     * @param string $html     the HTML markup to render
     * @param string $fullPath absolute path of the .pdf file to write
     * @return bool true when the PDF was written
     */
    public static function toFile($html, $fullPath)
    {
        // mPDF needs a writable temp dir; prefer the app data dir, fall back to
        // the system temp dir.
        $tempDir = BaseService::getInstance()->getDataDirectory() . 'mpdf_tmp';
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0775, true);
        }
        if (!is_dir($tempDir) || !is_writable($tempDir)) {
            $tempDir = sys_get_temp_dir();
        }

        $mpdf = new \Mpdf\Mpdf(array('tempDir' => $tempDir));
        $mpdf->WriteHTML((string) $html);
        $mpdf->Output($fullPath, \Mpdf\Output\Destination::FILE);

        return file_exists($fullPath);
    }
}
