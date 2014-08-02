<?php
namespace boundstate\htmlconverter;

use yii\helpers\ArrayHelper;

/**
 * HtmlToImageConverter converts HTML content to PDF using wkhtmtoimage.
 * @link http://wkhtmltopdf.org/
 *
 * It is used by [[ImageResponseFormatter]] to format response data.
 *
 * @author Bound State Software <info@boundstatesoftware.com>
 */
class HtmlToImageConverter extends BaseConverter
{
    /**
     * @var string path to the wkhtmltoimage binary
     */
    public $bin = '/usr/bin/wkhtmltoimage';

    /**
     * Converts HTML to an image file.
     * @param string $html HTML
     * @param array $options
     * @return string image file contents
     */
    public function convert($html, $options = [])
    {
        // Override any global options with locally specified options
        $options = ArrayHelper::merge($this->options, $options);

        // Generate temp HTML file
        $htmlFilename = $this->getTempFilename('html');
        $this->createHtmlFile($html, $htmlFilename);

        // Generate temp image file and get contents
        $imageFilename = $this->getTempFilename('png');
        $this->runCommand($htmlFilename, $imageFilename, $options);
        $data = @file_get_contents($imageFilename);

        // Cleanup
        @unlink($htmlFilename);
        @unlink($imageFilename);

        return $data;
    }
}