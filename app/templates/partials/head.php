<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($meta['title'] ?? $page->get('titre', 'Wonka Chocolate Factory') ?? 'Wonka Chocolate Factory', ENT_QUOTES, 'UTF-8') ?></title>
    <?php if (!empty($meta['description'])): ?>
    <meta name="description" content="<?= htmlspecialchars($meta['description'], ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <meta name="author" content="<?= htmlspecialchars($meta['author'] ?? 'Service communication Wonka', ENT_QUOTES, 'UTF-8') ?>">
    <?php autoload_styles($page ?? null); ?>
</head>
<body>
