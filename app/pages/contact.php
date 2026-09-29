<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $validator = new Validator($_POST);
    $valide = $validator->validate([
        'nom'     => ['required'],
        'email' => ['required', 'email'],
        'message' => ['required'],
    ]);
    var_dump($valide, $validator->getErrors());
}
?>

<h1>Contact</h1>

<form method="post">
    <input type="text" name="nom" placeholder="Nom">
    <input type="text" name="email" placeholder="Email">
    <textarea name="message" placeholder="Message"></textarea>
    <button type="submit" name="submit">Envoyer</button>
</form>