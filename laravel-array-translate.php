<?php
require __DIR__ . '/vendor/autoload.php';

use Nggit\Google\Translate;

$files = glob(__DIR__ . '/files-laravel-array/*.php');

foreach ($files as $file) {
    parseFile($file);
}

function parseFile($file)
{
    $translate = new Translate(array('lang' => array('en' => 'it')));
    $lines = file($file);
    $translation = '';
    $content = [];

    foreach ($lines as $pos => $line) {
        $content[$pos] = trim($line);
        if (strpos($line, "=> '") !== false) {
            list($null, $line) = explode('=> ', $line);
            $line = str_replace('\'', '', $line);
            $text = trim($line);

            if (trim($text) !== '') {
                $translate->setText($text);
                $translation = $translate->process()->getResults();
                if (is_array($translation)) {
                    $translation = $translation[0];
                }
                $translation = trim($translation);

                echo_live("\nTesto: " . $text);
                echo_live("\nTraduzione: " . $translation);
                echo_live("\n");
            }
        }
    }

    //$content = implode("\n", $content);
    //file_put_contents($file . "_trad", $content);
}

function echo_live($txt)
{
    // inizializzazione del buffer per l'output
    if (ob_get_level() == 0) {
        ob_start();
    }
    echo $txt;
    // invia il contenuto al buffer
    ob_flush();
    flush();
}
