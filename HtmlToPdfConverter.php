<?php
namespace boundstate\htmlconverter;

use yii\helpers\ArrayHelper;

/**
 * HtmlToPdfConverter converts HTML content to PDF using wkhtmtopdf.
 * @link http://wkhtmltopdf.org/
 *
 * It is used by [[PdfResponseFormatter]] to format response data.
 *
 * @author Bound State Software <info@boundstatesoftware.com>
 */
class HtmlToPdfConverter extends BaseConverter
{
    /**
     * @var string path to the wkhtmltopdf binary
     */
    public $bin = '/usr/bin/wkhtmltopdf';

    /**
     * Converts HTML to a PDF file.
     * @param string $html HTML
     * @param array $options options passed directly to wkhtmltopdf, with the exception of header-html and footer-html,
     * which should be HTML instead of URLs.
     * @return string PDF file contents
     */
    public function convert($html, $options = [])
    {
        // Override any global options with locally specified options
        $options = ArrayHelper::merge($this->options, $options);

        // Generate temp HTML file for content
        $contentFilename = $this->getTempFilename('html');
        $this->createHtmlFile($html, $contentFilename);

        // Generate temp HTML file for header (if specified)
        $headerFilename = null;
        if (isset($options['header-html'])) {
            $headerFilename = $this->getTempFilename('html');
            $this->createHtmlFile($options['header-html'], $headerFilename);
            $options['header-html'] = $headerFilename;
        }
        
        // Generate temp HTML file for footer (if specified)
        $footerFilename = null;
        if (isset($options['footer-html'])) {
            $footerFilename = $this->getTempFilename('html');
            $this->createHtmlFile($options['footer-html'], $footerFilename);
            $options['footer-html'] = $footerFilename;
        }

        // Generate temp PDF file and get contents
        $pdfFilename = $this->getTempFilename('pdf');
        $this->runCommand($contentFilename, $pdfFilename, $options);
        $data = @file_get_contents($pdfFilename);

        // Cleanup
        @unlink($contentFilename);
        if ($headerFilename !== null) {
            @unlink($headerFilename);
        }
        if ($footerFilename !== null) {
            @unlink($footerFilename);
        }
        @unlink($pdfFilename);

        return $data;
    }
}