# yii2-htmlconverter

  Extension for the Yii2 framework that converts HTML to PDF or images using [wkhtmltopdf].

## Installation

  This extensions relies on `wkhtmltopdf`.  Installation insructions are provided on the [wkhtmltopdf website] [wkhtmltopdf].

  The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

  Either run

    php composer.phar require --prefer-dist boundstate/yii2-htmlconverter "*"

  or add

    "boundstate/yii2-htmlconverter": "*"

  to the require section of your `composer.json` file.

## Usage

  Setup the components in your config:

    'htmlToPdf' => [
        'class' => 'boundstate\htmlconverter\HtmlToPdfConverter',
        'bin' => '/usr/bin/wkhtmltopdf',
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
    'htmlToImage' => [
        'class' => 'boundstate\htmlconverter\HtmlToImageConverter',
        'bin' => '/usr/bin/wkhtmltoimage',
    ],
    'response' => [
        'formatters' => [
            'pdf' => [
                'class' => 'boundstate\htmlconverter\PdfResponseFormatter',
                // Set a filename to download the response as an attachments (instead of displaying in browser)
                'filename' => 'attachment.pdf'
            ],
            'image' => [
                'class' => 'boundstate\htmlconverter\ImageResponseFormatter',
            ],
        ]
    ],

  Now you can format a response as a PDF:

    Yii::$app->response->format = 'pdf';

  Or format a response as an image:

    Yii::$app->response->format = 'image';

  You can also manually generate a PDF from HTML:

    $html = $this->render('hello-word');
    $header = $this->render('hello-world-header');
    $pdf = Yii::$app->htmlToPdf->convert($html, ['page-size' => 'A4', 'header-html' => $header]);

  Or manually generate an image from HTML:

    $html = $this->render('hello-word');
    $pdf = Yii::$app->htmlToImage->convert($html);

[wkhtmltopdf]: http://wkhtmltopdf.org/