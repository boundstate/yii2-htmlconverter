<?php
namespace boundstate\htmlconverter;

use yii\web\ResponseFormatterInterface;
use Yii;

/**
 * ImageResponseFormatter formats the given data into an image.
 *
 * @author Bound State Software <info@boundstatesoftware.com>
 */
class ImageResponseFormatter extends BaseFormatter implements ResponseFormatterInterface
{
    public $converter = 'htmlToImage';
    public $contentType = 'image/png';
}