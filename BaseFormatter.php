<?php
namespace boundstate\htmlconverter;

use yii\base\Component;
use yii\web\Response;
use yii\web\ResponseFormatterInterface;
use Yii;

/**
 * BaseFormatter is the base class for the PDF and image response formatters.
 *
 * @author Bound State Software <info@boundstatesoftware.com>
 */
abstract class BaseFormatter extends Component implements ResponseFormatterInterface
{
    /**
     * @var string the name of the app component to perform the conversion
     */
    public $converter;
    /**
     * @var string the Content-Type header for the response
     */
    public $contentType;
    /**
     * @var array options (overrides the converter options)
     */
    public $options = [];
    /**
     * @var string the filename if the response should be a file download
     */
    public $filename;

    /**
     * Formats the specified response.
     * @param Response $response the response to be formatted.
     */
    public function format($response)
    {
        $response->getHeaders()->set('Content-Type', $this->contentType);
        if ($this->filename !== null) {
            $response->getHeaders()->set('Content-Disposition', "attachment; filename=\"{$this->filename}\"");
        }
        $response->content = Yii::$app->{$this->converter}->convert($response->data, $this->options);
    }
}