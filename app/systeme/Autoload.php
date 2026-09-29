<?php
// filepath: c:\wamp64\www\static2frameworkv0.1\app\systeme\Autoload.php

define('PATH_APP', dirname(__DIR__));
define('PATH_CLASS', PATH_APP . '/systeme/class');
define('PATH_ROOT_JSON', PATH_APP . '/root.json');
define('PATH_TEMPLATES', PATH_APP . '/templates');
define('PATH_PAGES', PATH_APP . '/pages');
define('PATH_CSS', PATH_APP . '/css');
define('PATH_JS', PATH_APP . '/js');

// URL web du dossier app (sans slash final).
// Adapte à ton installation WAMP, ex. '/static2frameworkv0.1/app'.
// C est cette constante qui sert à construire les href des <link> CSS.
define('BASE_URL', '/static2frameworkv0.1/app');

foreach (glob(PATH_CLASS . '/*.php') as $file) {
    require_once $file;
}

/**
 * Nettoie un nom de style venu du JSON (anti path traversal).
 * Autorisé : lettres, chiffres, -, _ et / (sous-dossiers), extension .css ajoutée si absente.
 * Retourne '' si invalide.
 */
function sanitize_style_name(string $name): string
{
    $name = trim(str_replace('\\', '/', $name));
    if ($name === '' || strpos($name, '..') !== false) {
        return '';
    }
    $name = ltrim($name, '/');
    if (!preg_match('#^[a-zA-Z0-9_\-/]+(\.css)?$#', $name)) {
        return '';
    }
    if (!str_ends_with($name, '.css')) {
        $name .= '.css';
    }
    return $name;
}

/** Retourne le premier chemin existant (relatif à PATH_CSS), ou null. */
function find_style_file(array $tries): ?string
{
    foreach ($tries as $rel) {
        if ($rel !== '' && is_file(PATH_CSS . '/' . $rel)) {
            return $rel;
        }
    }
    return null;
}

/**
 * Charge les styles déclarés dans le JSON de la page.
 *
 * Schéma JSON attendu :
 *   "styles": { "page": "homepage", "components": ["panel", "badge"] }
 * Formes acceptées aussi : "styles": "homepage", "styles": ["pages/homepage"],
 * et "components": [...] au niveau racine du JSON.
 *
 * Ordre des link : css/global.css en premier (si présent, pour toutes les pages),
 * puis le style de la page, puis les styles des composants (peuvent surcharger).
 * Les href sont construits avec BASE_URL, ex. /static2frameworkv0.1/app/css/global.css.
 * Résolution : page X -> css/pages/X.css puis css/X.css ;
 * composant Y -> css/components/Y.css puis css/Y.css.
 * Un chemin avec / est pris tel quel, ex. "pages/homepage".
 * Les fichiers absents sont ignorés (commentaire HTML de diagnostic).
 *
 * Appel dans head.php : autoload_styles($page ?? null)
 */
function autoload_styles($page = null): void
{
    $styles = [];
    $components = [];

    if ($page instanceof Page) {
        $styles = $page->get('styles', []);
        $components = $page->get('components', []);
    } elseif (is_array($page)) {
        $styles = $page['styles'] ?? [];
        $components = $page['components'] ?? [];
    }

    $pageStyles = [];
    if (is_string($styles)) {
        $pageStyles = [$styles];
    } elseif (is_array($styles)) {
        if (array_key_exists('page', $styles) || array_key_exists('components', $styles)) {
            $p = $styles['page'] ?? [];
            $pageStyles = is_string($p) ? [$p] : (is_array($p) ? array_values($p) : []);
            $c = $styles['components'] ?? [];
            $extra = is_string($c) ? [$c] : (is_array($c) ? array_values($c) : []);
            $flatComponents = is_string($components) ? [$components] : (is_array($components) ? array_values($components) : []);
            $components = array_merge($extra, $flatComponents);
        } else {
            $pageStyles = array_values($styles); // liste plate
        }
    }
    if (is_string($components)) {
        $components = [$components];
    } elseif (!is_array($components)) {
        $components = [];
    }

    $tags = []; // lignes HTML dans l ordre : global, page, composants ensuite
    $done = []; // fichiers déjà émis (anti-doublons)

    // Thème global commun à toutes les pages, en premier.
    if (is_file(PATH_CSS . '/global.css')) {
        $done[] = 'global.css';
        $tags[] = '<link rel="stylesheet" href="'
            . htmlspecialchars(BASE_URL . '/css/global.css', ENT_QUOTES, 'UTF-8') . '">';
    }

    $add = function (string $raw, array $tries) use (&$tags, &$done): void {
        $found = find_style_file($tries);
        if ($found !== null) {
            if (!in_array($found, $done, true)) {
                $done[] = $found;
                $tags[] = '<link rel="stylesheet" href="'
                    . htmlspecialchars(BASE_URL . '/css/' . $found, ENT_QUOTES, 'UTF-8') . '">';
            }
        } elseif ($raw !== '') {
            $tags[] = '<!-- autoload: style introuvable "'
                . htmlspecialchars($raw, ENT_QUOTES, 'UTF-8') . '" -->';
        }
    };

    foreach ($pageStyles as $raw) {
        if (!is_string($raw)) {
            continue;
        }
        $clean = sanitize_style_name($raw);
        if ($clean === '') {
            continue;
        }
        if (strpos($clean, '/') !== false) {
            $add($raw, [$clean]); // chemin explicite, ex. pages/homepage.css
        } else {
            $add($raw, ['pages/' . $clean, $clean]);
        }
    }

    foreach ($components as $raw) {
        if (!is_string($raw)) {
            continue;
        }
        $clean = sanitize_style_name($raw);
        if ($clean === '') {
            continue;
        }
        if (strpos($clean, '/') !== false) {
            $add($raw, [$clean]);
        } else {
            $add($raw, ['components/' . $clean, $clean]);
        }
    }

    if ($tags !== []) {
        // La 1re ligne hérite de l indentation de l appel dans head.php,
        // les suivantes sont indentées ici.
        echo implode("\n    ", $tags) . "\n";
    }
}