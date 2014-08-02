<?php
namespace boundstate\htmlconverter;

use yii\base\Component;
use yii\base\Exception;
use yii\helpers\FileHelper;
use Yii;

/**
 * BaseConverter is the base class for converting HTML to PDF or images using wkhtmtox.
 * @link http://wkhtmltopdf.org/
 *
 * @author Bound State Software <info@boundstatesoftware.com>
 */
abstract class BaseConverter extends Component
{
    /**
     * @var string path to the binary
     */
    public $bin;
    /**
     * @var array default command line options
     */
    public $options = [];
    /**
     * @var string the directory to store temporary files during conversion. You may use path alias here.
     * If not set, it will use the "htmlconverter" subdirectory under the application runtime path.
     */
    public $tempPath = '@runtime/htmlconverter';
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
     * Initializes the converter and ensures the temp path exists.
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
     * Converts the HTML to the destination format.
     * @param string $html HTML
     * @param array $options
     * @return mixed
     */
    public abstract function convert($html, $options = []);

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
     * Runs the command.
     * @param string $htmlFilename HTML filename
     * @param string $destFilename destination filename
     * @param array $options
     * @throws Exception
     */
    protected function runCommand($htmlFilename, $destFilename, $options = [])
    {
        $command = $this->getCommand($htmlFilename, $destFilename, $options);
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
     * Returns the command to run.
     * @param string $htmlFilename
     * @param string $destFilename
     * @param array $options
     * @return string
     */
    protected function getCommand($htmlFilename, $destFilename, $options = [])
    {
        return $this->bin . ' ' . $this->getCommandOptions($options) . ' ' . $htmlFilename . ' ' . $destFilename;
    }

    /**
     * Returns options string for the  command.
     * @param array $options
     * @return string
     */
    protected function getCommandOptions($options)
    {
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