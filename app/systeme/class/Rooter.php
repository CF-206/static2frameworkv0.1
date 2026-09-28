<?php
class Rooter
{
    public function Run(): void
    {
        $url = $_GET['url'] ?? '/';
        $url = parse_url($url, PHP_URL_PATH) ?: '/';
        $urlParts = explode('/', trim($url, '/'));
        $method = strtolower($_SERVER['REQUEST_METHOD'] ?? 'get');

        $config = json_decode(file_get_contents(PATH_ROOT_JSON), true);
        $routes = $config['routes'][$method] ?? [];

        foreach ($routes as $route) {
            $routeParts = explode('/', trim($route['url'] ?? '', '/'));

            if (count($routeParts) !== count($urlParts)) {
                continue;
            }

            $params = [];
            $matches = true;

            foreach ($routeParts as $index => $part) {
                if (preg_match('/^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$/', $part, $match)) {
                    $params[$match[1]] = rawurldecode($urlParts[$index]);
                } elseif ($part !== $urlParts[$index]) {
                    $matches = false;
                    break;
                }
            }

            if (!$matches) {
                continue;
            }

            $page = PATH_APP . '/pages/' . ltrim($route['page'] ?? '', '/\\');

            if (!is_file($page)) {
                http_response_code(500);
                return;
            }

            require $page;
            return;
        }

        http_response_code(404);
    }
}