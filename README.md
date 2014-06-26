# yii2-pdf

  PDF extension for the Yii2 framework

## Installation

  The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

  Either run

    php composer.phar require --prefer-dist boundstate/yii2-pdf "*"

  or add

    "boundstate/yii2-pdf": "*"

  to the require section of your `composer.json` file.

## Usage

  Setup the components in your config:

    'htmlToPdf' => [
        'class' => 'boundstate\pdf\HtmlToPdfConverter',
        'bin' => '/usr/sbin/wkhtmltopdf',
        // global wkhtmltopdf command line options
        // (see http://wkhtmltopdf.org/usage/wkhtmltopdf.txt)
        'options' => [
            'print-media-type',
            'disable-smart-shrinking',
            'no-outline',
            'page-size' => 'letter',
            'load-error-handling' => 'ignore',
            'load-media-error-handling' => 'ignore'
        ],
    ],
    'response' => [
        'formatters' => [
            'pdf' => [
                'class' => 'boundstate\pdf\PdfResponseFormatter',
                // Set a filename to download the response as an attachments (instead of displaying in browser)
                'filename' => 'attachment.pdf'
            ],
        ]
    ],

  Now you can format a response as a PDF:

    Yii::$app->response->format = 'pdf';

  Or manually generate a PDF from HTML:

    $html = $this->render('hello-word');
    $pdf = Yii::$app->htmlToPdf->convert($html, ['page-size' => 'A4']);

