<?php
require __DIR__ . '/vendor/autoload.php';

use Nggit\Google\Translate;

// Variabile per memorizzare l'eventuale errore nell'input dell'utente
$error = '';

// Se è stato inviato un file da tradurre
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['file']['tmp_name'];
        $file_name = $_FILES['file']['name'];

        // Controlla se il file è un file .pot
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        if (!in_array($file_ext, ['po', 'pot'])) {
            echo '<p style="color: darkred;">Il file deve essere un file .po oppure .pot</p>';
            showPage();
        } else {
            $data = parseFile($file_tmp);
            download($data['content'], $file_name);
        }
    } else {
        $error = 'Si è verificato un errore durante il caricamento del file.';
    }
} else {
    showPage();
}

function parseFile($file_tmp)
{
    $translate = new Translate(array('lang' => array('en' => 'it')));
    $lines = file($file_tmp);
    $translation = '';
    $content = [];
    $toTranslate = '';
    $translations = 0;

    foreach ($lines as $pos => $line) {
        $content[$pos] = trim($line);
        if (strpos($line, 'msgid "') !== false) {
            $toTranslate = $line;
        } elseif (strpos($line, 'msgstr ""') !== false) {
            $text = str_replace('msgid "', '', $toTranslate);
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

                $content[$pos] = 'msgstr "' . $translation . '"';
                $translations++;
            }
        }
    }

    $content = implode("\n", $content);

    return [
        'content' => $content,
        'translations' => $translation
    ];
}

function download($content, $file_name)
{

    // Forza il download del file
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file_name) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . strlen($content));
    
    // Output del contenuto del file tradotto
    echo $content;
    exit;
}

function showPage()
{
    $page = <<<EOT
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Traduzione File .po / .pot</title>
    </head>
    <body>
        <h1>Traduzione File .po / .pot</h1>

        <form method="post" enctype="multipart/form-data" action="">
            <label for="file">Seleziona il file da tradurre:</label><br>
            <input type="file" id="file" name="file" accept=".po, .pot"><br>
            <small>Esempio: nomefile.pot</small><br><br>
            <input type="submit" value="Traduci">
        </form>
    </body>
    </html>
    EOT;
    echo $page;
}
