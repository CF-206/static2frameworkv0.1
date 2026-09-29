<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <?php if ($description): ?>
    <meta name="description" content="<?= $description ?>">
    <?php endif; ?>
</head>
<body>
    <?= $content ?>
</body>
</html>
