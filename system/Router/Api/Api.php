<?php

namespace System\Router\Api;

class Api
{
    public static function get($uri, $controller, $name = null)
    {
        $executeMethod = explode('@', $controller);
        $class = $executeMethod[0];
        $method = $executeMethod[1];
        global $routes;
        $routes['get'][] = [
            'url' => 'api/' . trim($uri, '/'),
            'class' => $class,
            'method' => $method,
            'name' => $name
        ];
    }

    public static function post($uri, $controller, $name = null)
    {
        $executeMethod = explode('@', $controller);
        $class = $executeMethod[0];
        $method = $executeMethod[1];
        global $routes;
        $routes['post'][] = [
            'url' => 'api/' . trim($uri, '/'),
            'class' => $class,
            'method' => $method,
            'name' => $name
        ];
    }
}