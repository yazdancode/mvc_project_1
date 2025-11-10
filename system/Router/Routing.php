<?php

namespace System\Router;

class Routing
{
    private $current_route;
    private $method_field;
    private $routes;
    private $values = [];

    public function __construct()
    {
        $this->current_route = explode('/', trim(CURRENT_ROUTE, '/'));
        $this->method_field = $this->methodField();
        global $routes;
        $this->routes = $routes;
    }

    public function methodField()
    {
        $method_field = strtolower($_SERVER['REQUEST_METHOD']);

        if ($method_field === 'post' && isset($_POST['_method'])) {
            $override = strtolower($_POST['_method']);
            if (in_array($override, ['put', 'delete'])) {
                $method_field = $override;
            }
        }

        return $method_field;
    }

    public function run()
    {
        $match = $this->match();
        if (empty($match)) {
            $this->error404();
        }

        $classPath = str_replace('\\', '/', $match['class']);
        $path = BASE_DIR . "/App/Http/Controllers/" . $classPath . ".php";

        if (!file_exists($path)) {
            $this->error404();
        }

        require_once $path;

        $controller = new $match['class'];
        call_user_func_array([$controller, $match['method']], $this->values);
    }

    public function match()
    {
        $reserveRoutes = isset($this->routes[$this->method_field]) ? $this->routes[$this->method_field] : [];

        foreach ($reserveRoutes as $reserveRoute) {
            if ($this->compare($reserveRoute['url'])) {
                return [
                    'class' => $reserveRoute['class'],
                    'method' => $reserveRoute['method']
                ];
            } else {
                $this->values = [];
            }
        }

        return [];
    }

    public function compare($reserveRouteUrl)
    {
        $reserveRouteUrl = trim($reserveRouteUrl, '/');

        if ($reserveRouteUrl === '') {
            return isset($this->current_route[0]) && trim($this->current_route[0], '/') === '';
        }

        $reserveRouteUrlArray = explode('/', $reserveRouteUrl);

        if (count($this->current_route) !== count($reserveRouteUrlArray)) {
            return false;
        }

        foreach ($reserveRouteUrlArray as $index => $segment) {
            if (preg_match('/^{\w+}$/', $segment)) {
                $this->values[] = $this->current_route[$index];
            } elseif (!isset($this->current_route[$index]) || $segment !== $this->current_route[$index]) {
                return false;
            }
        }

        return true;
    }

    public function error404()
    {
        http_response_code(404);
        include __DIR__ . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . '404.php';
        exit;
    }
}
