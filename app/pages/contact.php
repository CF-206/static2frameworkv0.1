<?php
$erreurs = [];
$envoye = isset($_GET['envoye']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $validator = new Validator($_POST);

    $valide = $validator->validate([
        'nom'     => ['required'],
        'email'   => ['required', 'email'],
        'message' => ['required'],
    ]);

    if ($valide) {
        $donnees = $validator->getData();
        $fichier = PATH_APP . '/data/contact_messages.json';

        // 1. Lire les messages déjà enregistrés
        $messages = is_file($fichier)
            ? json_decode(file_get_contents($fichier), true) ?? []
            : [];

        // 2. Ajouter le nouveau message à la fin
        $messages[] = [
            'nom'     => $donnees['nom'],
            'email'   => $donnees['email'],
            'message' => $donnees['message'],
            'date'    => date('Y-m-d H:i:s'),
        ];

        // 3. Réécrire tout le fichier
        file_put_contents(
            $fichier,
            json_encode($messages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );

        header('Location: ' . BASE_URL . '/contact?envoye=1');
        exit;
    } else {
        $erreurs = $validator->getErrors();
    }
}
?>

<section class="contact">
    <h1>Contact</h1>

    <?php if ($envoye): ?>
        <p class="contact__succes">Merci, votre message a bien été envoyé.</p>
    <?php endif; ?>

    <?php foreach ($erreurs as $message): ?>
        <p class="contact__erreur"><?= htmlspecialchars($message) ?></p>
    <?php endforeach; ?>

    <form class="contact__form" method="post">
        <input type="text" name="nom" placeholder="Nom">
        <input type="text" name="email" placeholder="Email">
        <textarea name="message" placeholder="Message"></textarea>
        <button type="submit" name="submit">Envoyer</button>
    </form>
</section>