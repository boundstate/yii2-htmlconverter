<?php
namespace boundstate\pdf;

use yii\base\Component;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use Yii;

/**
 * HtmlToPdfConverter converts HTML content to PDF using wkhtmtopdf.
 * @link http://wkhtmltopdf.org/
 *
 * It is used by [[PdfResponseFormatter]] to format response data.
 *
 * @author Bound State Software <info@boundstatesoftware.com>
 */
class HtmlToPdfConverter extends Component
{
    /**
     * @var string path to the wkhtmltopdf binary
     */
    public $bin = '/usr/bin/wkhtmltopdf';
    /**
     * @var array default command line options for wkhtmltopdf
     */
    public $options = [];
    /**
     * @var string the directory to store temporary files during PDF generation. You may use path alias here.
     * If not set, it will use the "pdf" subdirectory under the application runtime path.
     */
    public $tempPath = '@runtime/pdf';
    /**
     * @var integer the permission to be set for newly created cache files.
     * This value will be used by PHP chmod() function. No umask will be applied.
     * If not set, the permission will be determined by the current environment.
     */
    public $fileMode;
    /**
     * @var integer the permission to be set for newly created directories.
     * This value will be used by PHP chmod() function. No umask will be applied.
     * Defaults to 0775, meaning the directory is read-writable by owner and group,
     * but read-only for other users.
     */
    public $dirMode = 0775;

    /**
     * Initializes the PDF Generator and ensures the temp path exists.
     */
    public function init()
    {
        parent::init();
        $this->tempPath = Yii::getAlias($this->tempPath);
        if (!is_dir($this->tempPath)) {
            FileHelper::createDirectory($this->tempPath, $this->dirMode, true);
        }
    }

    /**
     * Converts HTML to a PDF file.
     * @param string $html HTML
     * @param array $options command line options
     * @return string PDF file contents
     */
    public function convert($html, $options = [])
    {
        // TODO handle failures gracefully

        // Generate temp HTML file
        $htmlFilename = $this->getTempFilename('html');
        $this->createHtmlFile($html, $htmlFilename);

        // Generate temp PDF file and get contents
        $pdfFilename = $this->getTempFilename('pdf');
        $this->createPdfFile($htmlFilename, $pdfFilename, $options);
        $data = @file_get_contents($pdfFilename);

        // Cleanup
        @unlink($htmlFilename);
        @unlink($pdfFilename);

        return $data;
    }

    /**
     * Creates a temporary HTML file.
     * @param string $html
     * @param string $htmlFilename filename
     * @return bool
     */
    protected function createHtmlFile($html, $htmlFilename)
    {
        if (@file_put_contents($htmlFilename, $html, LOCK_EX) !== false) {
            if ($this->fileMode !== null) {
                @chmod($htmlFilename, $this->fileMode);
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Create a temporary PDF file
     * @param string $htmlFilename HTML filename
     * @param string $pdfFilename PDF filename
     * @param array $options
     * @throws Exception
     */
    protected function createPdfFile($htmlFilename, $pdfFilename, $options = [])
    {
        $command = $this->getCommand($htmlFilename, $pdfFilename, $options);
        $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);

        if (!is_resource($process))
            throw new Exception("Could not run command $command");

        // Get stdout and stderr from pipes and then close them
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $result = proc_close($process);

        // Expect command to exit with code 0 (or code 1 due to an http error)
        if ($result !== 0 && $result !== 1)
            throw new Exception("Could not run command $command:\n$stderr");
    }

    /**
     * Returns the wkhtmltopdf command to run.
     * @param string $htmlFilename
     * @param string $pdfFilename
     * @param array $options
     * @return string
     */
    protected function getCommand($htmlFilename, $pdfFilename, $options = [])
    {
        return $this->bin . ' ' . $this->getCommandOptions($options) . ' ' . $htmlFilename . ' ' . $pdfFilename;
    }

    /**
     * Returns options string for wkhtmltopdf command.
     * @param array $options
     * @return string
     */
    protected function getCommandOptions($options)
    {
        // Override any global options with locally specified options
        $options = ArrayHelper::merge($this->options, $options);

        $out = '';
        foreach ($options as $key => $val) {
            $out .= is_numeric($key) ? " --$val" : " --$key $val";
        }
        return $out;
    }

    /**
     * Returns a temporary filename.
     * @param string $extension file extension
     * @return string
     */
    protected function getTempFilename($extension)
    {
        return $this->tempPath . DIRECTORY_SEPARATOR . uniqid('', true) . '.' . $extension;
    }
}