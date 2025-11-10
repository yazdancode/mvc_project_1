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

        if ($method_field === 'post') {
            if (isset($_POST['_method'])) {
                $override = strtolower($_POST['_method']);
                if (in_array($override, ['put', 'delete'])) {
                    $method_field = $override;
                }
            }
        }
        return $method_field;
    }

    public function run()
    {
    }

    public function match()
    {
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
            if (!isset($this->current_route[$index]) || $segment !== $this->current_route[$index]) {
                return false;
            }
        }
        return true;
    }


    public function error404()
    {
        http_response_code(404);
        include __DIR__ . DIRECTORY_SEPARATOR . 'View' .DIRECTORY_SEPARATOR .'404.php';
        exit;
    }
}
