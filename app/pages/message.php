<?php
$fichier  = PATH_APP . '/data/contact_message.json';
$messages = is_file($fichier)
    ? json_decode(file_get_contents($fichier), true) ?? []
    : [];
?>

<section class="chat">
    <h1>Messages reçus</h1>

    <?php if ($messages === []): ?>
        <p>Aucun message pour l'instant.</p>
    <?php endif; ?>

    <?php foreach ($messages as $m): ?>
        <article class="chat__bulle">
            <p class="chat__auteur">
                <?= htmlspecialchars($m['nom'] ?? '') ?>
                <span><?= htmlspecialchars($m['date'] ?? '') ?></span>
            </p>
            <p class="chat__texte"><?= nl2br(htmlspecialchars($m['message'] ?? '')) ?></p>
        </article>
    <?php endforeach; ?>
</section>