<?php
class Page
{
    private string $name;
    private array $data = [];
    private array $params = [];

    public function __construct(string $name, array $params = [])
    {
        // Normalise : "homepage.php", "/homepage", "pages/homepage" -> "homepage"
        $name = trim($name);
        $name = basename($name, '.php');
        $name = trim($name, "/\\");
        $this->name = $name;
        $this->params = $params;
        $this->loadJson();
    }

    private function loadJson(): void
    {
        $jsonFile = PATH_PAGES . '/json/' . $this->name . '.json';

        if (!is_file($jsonFile)) {
            return;
        }

        $raw = file_get_contents($jsonFile);
        if ($raw === false) {
            return;
        }

        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $this->data = $decoded;
        }
    }

    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->data;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getParam(string $key, $default = null)
    {
        return $this->params[$key] ?? $default;
    }

    /** Valeur échappée pour injection dans une balise HTML. */
    public function e(string $key, $default = ''): string
    {
        return htmlspecialchars((string) $this->get($key, $default), ENT_QUOTES, 'UTF-8');
    }

    /** Valeur brute (HTML déjà sûr depuis le JSON). */
    public function raw(string $key, $default = ''): string
    {
        $value = $this->get($key, $default);
        if (is_array($value)) {
            return '';
        }
        return (string) $value;
    }

    /**
     * Construit les metas <head> depuis le JSON.
     * Convention : bloc "meta": { "title", "description", ... }
     * avec repli sur les clés racine "titre"/"title", "description"/"sous_titre".
     */
    private function buildMeta(): array
    {
        $meta = $this->get('meta', []);
        if (!is_array($meta)) {
            $meta = [];
        }

        return [
            'title' => $meta['title']
                ?? $this->get('titre')
                ?? $this->get('title')
                ?? ucfirst(str_replace(['-', '_'], ' ', $this->name)),
            'description' => $meta['description']
                ?? $this->get('description')
                ?? $this->get('sous_titre')
                ?? '',
            'author' => $meta['author'] ?? 'Service communication Wonka',
        ];
    }

    /**
     * Injecte les infos du JSON dans les balises du page.php du même nom.
     *
     * Deux mécanismes complémentaires :
     *  1. Variables PHP : extract(data) + echo $titre, ou echo $page->e('titre').
     *  2. Placeholders auto : {{titre}}, {{meta.description}} (échappé)
     *     et {{{cle}}} (brut HTML), remplacés après rendu.
     */
    public function render(): void
    {
        $pageFile = PATH_PAGES . '/' . $this->name . '.php';

        if (!is_file($pageFile)) {
            http_response_code(404);
            require PATH_APP . '/pages/erreur.php';
            return;
        }

        $page = $this; // exposé aux templates : $page->e('titre'), $page->get('items')
        $data = $this->data;
        $meta = $this->buildMeta();
        $params = $this->params;

        // Variables à plat du JSON -> utilisables comme echo $titre.
        // EXTR_SKIP : ne jamais écraser $page, $data, $meta, $params.
        extract($data, EXTR_SKIP);

        ob_start();
        require PATH_TEMPLATES . '/partials/head.php';
        require PATH_TEMPLATES . '/partials/header.php';
        require $pageFile;
        require PATH_TEMPLATES . '/partials/footer.php';
        $html = ob_get_clean();

        echo $this->injectPlaceholders($html === false ? '' : $html);
    }

    /**
     * Remplace {{cle.nichee}} (échappé) et {{{cle.nichee}}} (brut)
     * par la valeur correspondante du JSON.
     */
    private function injectPlaceholders(string $html): string
    {
        // Triple accolade d'abord (brut, pour blocs HTML du JSON).
        $html = preg_replace_callback(
            '/\{\{\{\s*([a-zA-Z0-9_\.\-]+)\s*\}\}\}/',
            function (array $m): string {
                $value = $this->get($m[1], '');
                if (is_array($value)) {
                    return '';
                }
                return (string) $value;
            },
            $html
        );

        // Double accolade (échappé, pour texte dans balises).
        return preg_replace_callback(
            '/\{\{\s*([a-zA-Z0-9_\.\-]+)\s*\}\}/',
            function (array $m): string {
                $value = $this->get($m[1], '');
                if (is_array($value)) {
                    return '';
                }
                return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
            },
            $html
        ) ?? $html;
    }
}
