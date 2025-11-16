<?php

namespace System\Router\Web;

class Route
{
    public static function get($uri, $controller, $name = null): void
    {
        $executeMethod = explode('@', $controller);
        $class = $executeMethod[0];
        $method = $executeMethod[1];
        global $routes;
        $routes['get'][] = [
            'url' => trim($uri, '/'),
            'class' => $class,
            'method' => $method,
            'name' => $name
        ];
    }

    public static function post($uri, $controller, $name = null): void
    {
        $executeMethod = explode('@', $controller);
        $class = $executeMethod[0];
        $method = $executeMethod[1];
        global $routes;
        $routes['post'][] = [
            'url' => trim($uri, '/'),
            'class' => $class,
            'method' => $method,
            'name' => $name
        ];
    }

    public static function put($uri, $controller, $name = null): void
    {
        $executeMethod = explode('@', $controller);
        $class = $executeMethod[0];
        $method = $executeMethod[1];
        global $routes;
        $routes['put'][] = [
            'url' => trim($uri, '/'),
            'class' => $class,
            'method' => $method,
            'name' => $name
        ];
    }

    public static function delete($uri, $controller, $name = null): void
    {
        $executeMethod = explode('@', $controller);
        $class = $executeMethod[0];
        $method = $executeMethod[1];
        global $routes;
        $routes['delete'][] = [
            'url' => trim($uri, '/'),
            'class' => $class,
            'method' => $method,
            'name' => $name
        ];
    }
}
