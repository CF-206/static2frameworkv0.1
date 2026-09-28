<?php

require __DIR__ . '/systeme/Autoload.php';

$chemin = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$dossier = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if (str_starts_with($chemin, $dossier)) {
    $chemin = substr($chemin, strlen($dossier));
}
if ($chemin === '') {
    $chemin = '/';
}
$rooter = new Rooter();
$rooter->lancer($chemin);