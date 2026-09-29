<div class="wrap">
    <!-- 1. Variable PHP issue de extract($data) -->
    <h1 class="titre"><?= htmlspecialchars($titre ?? '') ?></h1>
    <!-- 2. Via l'objet Page (échappement intégré, dot-notation possible) -->
    <p class="sous-titre"><?= $page->e('sous_titre') ?></p>
    <p><?= $page->e('description') ?></p>
</div>
