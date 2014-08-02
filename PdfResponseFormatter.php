<?php
namespace boundstate\htmlconverter;

use yii\web\ResponseFormatterInterface;
use Yii;

/**
 * PdfResponseFormatter formats the given data into a PDF document.
 *
 * @author Bound State Software <info@boundstatesoftware.com>
 */
class PdfResponseFormatter extends BaseFormatter implements ResponseFormatterInterface
{
    public $converter = 'htmlToPdf';
    public $contentType = 'application/pdf';
}