<?php
$messages = [
    404 => "Cette page n'existe pas.",
    500 => "Une erreur est survenue sur le serveur.",
];
$message = $messages[$code] ?? "Une erreur est survenue.";
$accueil = dirname($_SERVER['SCRIPT_NAME']) . '/';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Erreur <?= $code ?> | Wonka Chocolate Factory</title>
</head>
<body>
    <h1>Erreur <?= $code ?></h1>
    <p><?= $message ?></p>
    <a href="<?= $accueil ?>">Retour à l'accueil</a>
</body>
</html>