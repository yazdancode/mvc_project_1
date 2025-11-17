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
            $className = static::class;
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

    protected function arrayToObjects(array $array): void
    {
        $collection = [];
        foreach ($array as $value) {
            $object = $this->arrayToAttributes($value);
            $collection[] = $object;
        }
        $this->collection = $collection;
    }

    protected function inHiddenAttributes($attribute): bool
    {
        return in_array($attribute, $this->hidden ?? [], true);
    }

    private function inCastsAttributes($attribute): bool
    {
        return isset($this->casts[$attribute]);
    }

    /**
     * Decode a value according to the cast type.
     *
     * @param string $attributeKey
     * @param mixed $value
     * @return mixed
     */
    private function castDecodeValue(string $attributeKey, $value)
    {
        if (!isset($this->casts[$attributeKey])) {
            return $value;
        }

        $type = $this->casts[$attributeKey];

        if ($type === 'array' || $type === 'object') {
            // empty values fallback
            if ($value === null || $value === '') {
                return $type === 'array' ? [] : null;
            }

            // use allowed_classes => false for safety (no PHP objects will be instantiated)
            // suppress warnings if any and return original value on failure
            $decoded = @unserialize($value, ['allowed_classes' => false]);

            // unserialize returns false on failure but false can be a valid value (b:0;)
            // handle that case: check serialized form for boolean false
            if ($decoded === false && $value !== 'b:0;') {
                return $value;
            }

            return $decoded;
        }

        // other cast types can be added here (json, int, bool, datetime...)
        return $value;
    }

    /**
     * Encode a value according to the cast type (for storage).
     *
     * @param string $attributeKey
     * @param mixed $value
     * @return mixed
     */
    private function castEncodeValue(string $attributeKey, $value)
    {
        if (!isset($this->casts[$attributeKey])) {
            return $value;
        }

        $type = $this->casts[$attributeKey];

        if ($type === 'array' || $type === 'object') {
            // ensure non-scalar values are serialized; scalars can still be serialized safely
            return serialize($value);
        }

        // other cast types can be handled (json => json_encode, bool => (bool), etc.)
        return $value;
    }

    /**
     * Apply casts (encode) to an associative array of values.
     *
     * @param array $values
     * @return array
     */
    private function arrayToCastEncodeValue(array $values): array
    {
        $newArray = [];

        foreach ($values as $attribute => $value) {
            if ($this->inCastsAttributes($attribute)) {
                $newArray[$attribute] = $this->castEncodeValue($attribute, $value);
            } else {
                $newArray[$attribute] = $value;
            }
        }

        return $newArray;
    }
}
