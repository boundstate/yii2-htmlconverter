<?php
namespace boundstate\pdf;

use yii\base\Component;
use yii\web\Response;
use yii\web\ResponseFormatterInterface;
use Yii;

/**
 * PdfResponseFormatter formats the given data into a PDF document.
 *
 * @author Bound State Software <info@boundstatesoftware.com>
 */
class PdfResponseFormatter extends Component implements ResponseFormatterInterface
{
    /**
     * @var string the Content-Type header for the response
     */
    public $contentType = 'application/pdf';
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
        $response->content = Yii::$app->htmlToPdf->convert($response->data);
    }
}