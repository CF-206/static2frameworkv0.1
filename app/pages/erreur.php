<?php
$messages = [
    404 => "Même les Oompa-Loompas n'ont pas trouvé cette page.",
    500 => "Une machine de l'usine a fait « pouf ». On envoie quelqu'un.",
];
$message = $messages[$code] ?? "Quelque chose s'est mal passé dans l'usine.";
$accueil = dirname($_SERVER['SCRIPT_NAME']) . '/';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur <?= $code ?> | Wonka Chocolate Factory</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/global.css">
</head>
<body>
    <main class="erreur">
        <h1 class="erreur__code">
            <?php foreach (str_split((string) $code) as $chiffre): ?>
                <span><?= $chiffre ?></span>
            <?php endforeach; ?>
        </h1>
        <p class="erreur__message"><?= $message ?></p>
        <a class="erreur__lien" href="<?= BASE_URL ?>/">Retour à l'accueil</a>
    </main>
</body>
</html>