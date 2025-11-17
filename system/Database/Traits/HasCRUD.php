<?php

namespace System\Database\Traits;

use System\Database\DBConnection\DBConnection;

trait HasCRUD
{
    protected function fill(): string
    {
        $fillArray = [];

        foreach ($this->fillable as $attribute) {
            if (property_exists($this, $attribute) && $this->$attribute !== null) {
                $fillArray[] = $this->getAttributeName($attribute) . " = ?";
                if ($this->inCastsAttributes($attribute)) {
                    $this->addValue(
                        $attribute,
                        $this->castEncodeValue($attribute, $this->$attribute)
                    );
                } else {
                    $this->addValue($attribute, $this->$attribute);
                }
            }
        }
        return implode(', ', $fillArray);
    }
}
