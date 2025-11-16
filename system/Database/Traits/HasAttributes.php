<?php

namespace System\Database\Traits;

trait HasAttributes
{
    private function registerAttribute($object, string $attribute, $value): void
    {
        if ($this->inCastsAttributes($attribute)) {
            $object->$attribute = $this->castDecodeValue($attribute, $value);
        } else {
            $object->$attribute = $value;
        }
    }

    protected function arrayToAttributes(array $array, $object = null): object
    {
        if (!$object) {
            $className = get_called_class();
            $object = new $className();
        }

        foreach ($array as $attribute => $value) {
            if ($this->inHiddenAttributes($attribute)) {
                continue;
            }
            $this->registerAttribute($object, $attribute, $value);
        }
        return $object;
    }

    protected function arrayToObjects()
    {

    }

    protected function inHiddenAttributes()
    {

    }

    private function inCastsAttributes()
    {

    }

    private function castDecodeValue()
    {

    }

    private function castEncodeValue()
    {

    }

    private function arrayToCastEncodeValue()
    {

    }
}
