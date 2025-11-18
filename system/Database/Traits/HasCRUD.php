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

    protected function saveMethod(): self
    {
        $fillString = $this->fill();

        if (!isset($this->{$this->primaryKey})) {
            $this->setSql(
                "INSERT INTO " . $this->getTableName() .
                " SET $fillString, " . $this->getAttributeName($this->createdAt) . " = NOW()"
            );
        } else {
            $this->setSql(
                "UPDATE " . $this->getTableName() .
                " SET $fillString, " . $this->getAttributeName($this->updatedAt) . " = NOW()"
            );
            $this->setWhere("AND", $this->getAttributeName($this->primaryKey) . " = ?");
            $this->addValue($this->primaryKey, $this->{$this->primaryKey});
        }

        $this->executeQuery();
        $this->resetQuery();

        if (!isset($this->{$this->primaryKey})) {
            $object = $this->findMethod(DBConnection::getInstance()->lastInsertId());
            $defaultVars = get_class_vars(static::class);
            $allVars = get_object_vars((object)$object);
            $differentVars = array_diff(array_keys($allVars), array_keys($defaultVars));

            foreach ($differentVars as $attribute) {
                if ($this->inCastsAttributes($attribute)) {
                    $this->registerAttribute($this, $attribute, $this->castEncodeValue($attribute, $object->$attribute));
                } else {
                    $this->registerAttribute($this, $attribute, $object->$attribute);
                }
            }
        }

        $this->resetQuery();
        $this->setAllowedMethods(['update', 'delete', 'find']);
        return $this;
    }

    protected function deleteMethod($id = null): bool
    {
        $object = $this;
        $this->resetQuery();

        if ($id) {
            $object = $this->findMethod($id);
            $this->resetQuery();
        }

        if (!$object) {
            return false;
        }

        $object->setSql("DELETE FROM " . $object->getTableName());
        $object->setWhere("AND", $this->getAttributeName($this->primaryKey) . " = ?");
        $object->addValue($object->primaryKey, $object->{$object->primaryKey});

        return (bool) $object->executeQuery();
    }
}
