<?php

namespace System\Database\Traits;

use BadMethodCallException;

trait HasMethodCaller
{
    public function __call(string $name, array $arguments)
    {
        return $this->methodCaller($this, $name, $arguments);
    }

    public static function __callStatic(string $name, array $arguments)
    {
        $className = static::class;
        $instance = new $className();
        return $instance->methodCaller($instance, $name, $arguments);
    }

    private array $allMethods = [
        'create', 'update', 'delete', 'find', 'all', 'save', 'where',
        'whereOr', 'whereIn', 'whereNull', 'whereNotNull', 'limit',
        'orderBy', 'get', 'paginate'
    ];

    private array $allowedMethods = [
        'create', 'update', 'delete', 'find', 'all', 'save', 'where',
        'whereOr', 'whereIn', 'whereNull', 'whereNotNull', 'limit',
        'orderBy', 'get', 'paginate'
    ];

    private function methodCaller(object $object, string $method, array $args)
    {
        $suffix = 'Method';
        $methodName = $method . $suffix;

        if (in_array($method, $this->allowedMethods, true)) {
            return call_user_func_array([$object, $methodName], $args);
        }

        throw new BadMethodCallException("Method '$method' is not allowed.");
    }

    protected function setAllowedMethods(array $array): void
    {
        $this->allowedMethods = $array;
    }

    protected function addAllowedMethod(string $method): void
    {
        if (!in_array($method, $this->allowedMethods, true)) {
            $this->allowedMethods[] = $method;
        }
    }
}
