# Wonka Chocolate Framework

## Prérequis

- PHP 8 ou plus
- Apache avec `mod_rewrite` activé et `AllowOverride All`
  - MAMP : dans `/Applications/MAMP/conf/apache/httpd.conf`, enlever le `#` devant
    `LoadModule rewrite_module modules/mod_rewrite.so`, puis redémarrer MAMP
  - WAMP : clic sur l'icône → Apache → Modules → cocher `rewrite_module`

## Installation

1. Cloner le dépôt dans le dossier web du serveur :
   - MAMP : `/Applications/MAMP/htdocs/`
   - WAMP : `C:\wamp64\www\`
2. Vérifier la constante `BASE_URL` dans `app/systeme/Autoload.php`.
   Elle doit correspondre à l'adresse du dossier `app` :
   ```php
   define('BASE_URL', '/static2frameworkv0.1/app');
   ```
3. Copier `app/.env.example` en `app/.env` et le remplir si besoin.
4. Vérifier que le dossier `app/data/` est accessible en écriture (les messages de contact y sont enregistrés).
5. Ouvrir `http://localhost:8888/static2frameworkv0.1/app/` (MAMP) ou `http://localhost/static2frameworkv0.1/app/` (WAMP).

## Structure

```
static2frameworkv0.1/
├─ app/
│  ├─ index.php              point d'entrée unique, toutes les URL passent par lui
│  ├─ .htaccess              redirige les URL vers index.php, protège le .env
│  ├─ root.json              liste des routes (URL → page)
│  ├─ .env / .env.example    variables d'environnement (le .env n'est pas versionné)
│  ├─ .gitignore
│  ├─ readme.md
│  ├─ systeme/
│  │  ├─ Autoload.php        constantes (chemins, BASE_URL), chargement des classes, chargement des CSS
│  │  └─ class/
│  │     ├─ Rooter.php       lit l'URL, trouve la route, affiche la page ou une erreur 404/500
│  │     ├─ Page.php         charge le JSON de la page et assemble head + header + page + footer
│  │     ├─ Validator.php    vérifie les données des formulaires
│  │     └─ Env.php          lecture du .env (pas encore implémentée)
│  ├─ templates/
│  │  └─ partials/           head.php, header.php, footer.php, communs à toutes les pages
│  ├─ pages/
│  │  ├─ homepage.php        contenu de chaque page (sans head, header ni footer)
│  │  ├─ contact.php         formulaire de contact
│  │  ├─ message.php         affichage des messages reçus
│  │  ├─ erreur.php          page 404 / 500
│  │  └─ json/               contenu et réglages de chaque page (textes, meta, CSS)
│  │     ├─ homepage.json
│  │     ├─ contact.json
│  │     └─ message.json
│  ├─ css/
│  │  ├─ global.css          style commun, chargé sur toutes les pages
│  │  └─ pages/              un fichier CSS par page (homepage, contact, message)
│  ├─ js/                    scripts
│  ├─ sources/               images
│  └─ data/                  données écrites par le site, bloqué depuis le navigateur
│     ├─ .htaccess
│     └─ contact_message.json  messages envoyés depuis le formulaire de contact
└─ tmp/                      ancien site, pour comparer le rendu
```

## Comment une page s'affiche

1. Le navigateur demande `/contact`.
2. Le `.htaccess` envoie la requête vers `index.php?url=contact`.
   Les fichiers qui existent vraiment (CSS, images) sont servis directement.
3. `index.php` charge l'Autoload et lance le `Rooter`.
4. Le `Rooter` cherche `/contact` dans `root.json` (en GET ou en POST).
   - route trouvée → il crée la `Page` correspondante
   - route inconnue → page 404
   - route trouvée mais fichier manquant → page 500
5. `Page` lit `pages/json/contact.json`, puis affiche dans l'ordre :
   `head.php` → `header.php` → `pages/contact.php` → `footer.php`.
6. Dans le `head`, `autoload_styles()` ajoute `global.css` puis le CSS déclaré dans le JSON de la page.

## Ajouter une page

Exemple avec une page « Histoire » à l'adresse `/histoire`.

**1. Déclarer la route dans `root.json`**
```json
{ "url": "/histoire", "page": "histoire.php" }
```
À mettre dans `"get"` (et aussi dans `"post"` si la page contient un formulaire).

**2. Créer le contenu dans `pages/json/histoire.json`**
```json
{
    "titre": "Notre histoire",
    "texte": "Tout a commencé en 1971…",
    "meta": {
        "title": "Notre histoire | Wonka Chocolate Factory",
        "description": "L'histoire de la chocolaterie Wonka."
    },
    "styles": { "page": "histoire" }
}
```

**3. Créer la page `pages/histoire.php`**
```php
<section class="histoire">
    <h1><?= $page->e('titre') ?></h1>
    <p><?= $page->e('texte') ?></p>
</section>
```
`$page->e('cle')` affiche une valeur du JSON en la sécurisant (`htmlspecialchars`).
Pour une valeur imbriquée : `$page->e('meta.title')`.

**4. (Optionnel) Créer `css/pages/histoire.css`**
Il est chargé automatiquement grâce à `"styles": { "page": "histoire" }`.

Le header, le footer et le `<head>` sont ajoutés tout seuls : ne pas les réécrire dans la page.

## Formulaires

Les formulaires sont vérifiés côté serveur avec la classe `Validator` :

```php
$validator = new Validator($_POST);

$valide = $validator->validate([
    'nom'   => ['required'],
    'email' => ['required', 'email'],
]);

if ($valide) {
    $donnees = $validator->getData();   // données nettoyées (trim)
} else {
    $erreurs = $validator->getErrors(); // un message par champ en erreur
}
```

Règles disponibles :

| Règle      | Vérifie                           |
|------------|-----------------------------------|
| `required` | le champ n'est pas vide           |
| `email`    | le format de l'adresse email      |

Une règle inconnue déclenche une erreur, pour repérer tout de suite les fautes de frappe.

Pour ajouter une règle : créer une méthode privée du même nom dans `Validator.php`
et ajouter son nom dans `$reglesAutorisees`.

Après un envoi réussi, la page redirige (`/contact?envoye=1`) pour éviter qu'un rechargement renvoie le formulaire.

## Sécurité

- Le `.env` n'est pas versionné et n'est pas accessible depuis le navigateur.
- Le dossier `data/` est bloqué par son propre `.htaccess` et `data/contact_message.json` n'est pas versionné.
- Les textes affichés passent par `htmlspecialchars` (`$page->e()`).
- Les données des formulaires sont stockées brutes et échappées seulement à l'affichage.

## Conventions

- Noms de fichiers en minuscules, mots séparés par des tirets.
- Jamais de chemin écrit en dur pour les liens, le CSS ou les images : toujours `BASE_URL`.
- Pas de `<style>` dans les pages : le CSS va dans `css/`.
- Un commit = un changement précis, avec un message clair.
- Une branche par fonctionnalité, fusionnée dans `main` une fois testée.
