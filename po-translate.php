<?php
require __DIR__ . '/vendor/autoload.php';

use Nggit\Google\Translate;

// list all files in files-po/*.po
$files = glob(__DIR__ . '/files-po/*.po');

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
        if (strpos($line, 'msgid "') !== false) {
            $text = str_replace('msgid "', '', $line);
            $text = trim($text);
            $text = rtrim($text, '"');
            $text = trim($text, '"');

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
        } elseif (strpos($line, 'msgstr ""') !== false) {
            // write the translation in the file, in the same position
            $content[$pos] = 'msgstr "' . $translation . '"';
        }
    }

    $content = implode("\n", $content);
    file_put_contents($file . "_trad", $content);
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
