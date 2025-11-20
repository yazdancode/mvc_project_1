<?php

namespace System\Database\Traits;

use System\Database\DBConnection\DBConnection;
use System\Database\ORM\Model;

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
            // INSERT
            $this->setSql(
                "INSERT INTO " . $this->getTableName() .
                " SET $fillString, " . $this->getAttributeName($this->createdAt) . " = NOW()"
            );
        } else {
            // UPDATE
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
            $lastId = DBConnection::getInstance()->lastInsertId();
            $object = $this->findMethod($lastId);

            if ($object) {
                $defaultVars = get_class_vars(static::class);
                $allVars = get_object_vars($object);
                $differentVars = array_diff(array_keys($allVars), array_keys($defaultVars));

                foreach ($differentVars as $attribute) {
                    if ($this->inCastsAttributes($attribute)) {
                        $this->registerAttribute($this, $attribute, $this->castEncodeValue($attribute, $object->$attribute));
                    } else {
                        $this->registerAttribute($this, $attribute, $object->$attribute);
                    }
                }
            }
        }

        $this->resetQuery();
        $this->setAllowedMethods(['update', 'delete', 'save']);
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

    protected function allMethod(): array
    {
        $this->setSql("SELECT * FROM " . $this->getTableName());
        $statement = $this->executeQuery();
        $data = $statement->fetchAll();

        if ($data) {
            $this->arrayToObjects($data);
            return $this->collection;
        }
        return [];
    }

    protected function findMethod($id): ?self
    {
        $this->setSql("SELECT * FROM " . $this->getTableName() .
            " WHERE " . $this->getAttributeName($this->primaryKey) . " = ? LIMIT 1");
        $this->addValue($this->primaryKey, $id);

        $statement = $this->executeQuery();
        $data = $statement->fetch();

        $this->setAllowedMethods(['update', 'delete', 'save']);

        if ($data) {
            $this->arrayToAttributes($data);
            return $this;
        }

        return null;
    }

    protected function whereMethod($attribute, $firstValue, $secondValue = null): self
    {
        if ($secondValue === null) {
            $condition = $this->getAttributeName($attribute).' = ?';
            $this->addValue($attribute, $firstValue);
        } else {
            $condition = $this->getAttributeName($attribute).' '.$firstValue.' ?';
            $this->addValue($attribute, $secondValue);
        }

        $operator = 'AND';
        $this->setWhere($operator, $condition);
        $this->setAllowedMethods(['where', 'whereOr', 'whereIn', 'whereNull', 'whereNotNull',
        'limit', 'orderBy', 'get', 'paginate']);
        return $this;
    }

    protected function whereOrMethod($attribute, $firstValue, $secondValue = null): self
    {
        if ($secondValue === null) {
            $condition = $this->getAttributeName($attribute).' = ?';
            $this->addValue($attribute, $firstValue);
        } else {
            $condition = $this->getAttributeName($attribute).' '.$firstValue.' ?';
            $this->addValue($attribute, $secondValue);
        }

        $operator = 'OR';
        $this->setWhere($operator, $condition);
        $this->setAllowedMethods(['where', 'whereOr', 'whereIn', 'whereNull', 'whereNotNull',
            'limit', 'orderBy', 'get', 'paginate']);
        return $this;
    }


    protected function whereNullMethod($attribute): self
    {

        $condition = $this->getAttributeName($attribute).' IS NULL ';
        $operator = 'AND';
        $this->setWhere($operator, $condition);
        $this->setAllowedMethods(['where', 'whereOr', 'whereIn', 'whereNull', 'whereNotNull',
            'limit', 'orderBy', 'get', 'paginate']);
        return $this;
    }

    protected function whereNotNullMethod($attribute): self
    {

        $condition = $this->getAttributeName($attribute).' IS NOT NULL ';
        $operator = 'AND';
        $this->setWhere($operator, $condition);
        $this->setAllowedMethods(['where', 'whereOr', 'whereIn', 'whereNull', 'whereNotNull',
            'limit', 'orderBy', 'get', 'paginate']);
        return $this;
    }

    protected function whereInMethod(string $attribute, array $values): self
    {
        if (empty($values)) {
            return $this;
        }
        $placeholders = implode(', ', array_fill(0, count($values), '?'));

        $condition = $this->getAttributeName($attribute) . " IN ($placeholders)";

        $this->setWhere('AND', $condition);

        foreach ($values as $value) {
            $this->addValue($attribute, $value);
        }

        $this->setAllowedMethods([
            'where', 'whereOr', 'whereIn', 'whereNull', 'whereNotNull',
            'limit', 'orderBy', 'get', 'paginate'
        ]);

        return $this;
    }

    protected function orderByMethod($attribute, $expression): self
    {
        $this->setOrderBy($attribute, $expression);
        $this->setAllowedMethods(['limit', 'orderBy', 'get', 'paginate']);
        return $this;
    }

    protected function limitMethod($from, $number): self
    {
        $this->setLimit($from, $number);
        $this->setAllowedMethods(['limit', 'get', 'paginate']);
        return $this;
    }

    protected function getMethod(array $array = []): array
    {
        if ($this->sql === '') {
            if (empty($array)) {
                $fields = $this->getTableName().'.*';
            } else {
                foreach ($array as $key => $field) {
                    $array[$key] = $this->getAttributeName($field);

                }
                $fields = implode(',', $array);
            }
            $this->setSql("SELECT $fields FROM ".$this->getTableName());
        }
        $statement = $this->executeQuery();
        $data = $statement->fetchAll();
        if ($data) {
            $this->arrayToObjects($data);
            return $this->collection;
        }
        return [];
    }

    protected function paginateMethod(int $perPage): array
    {
        $totalRows = $this->getCount();
        $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $totalPages = (int) ceil($totalRows / $perPage);
        $currentPage = max($currentPage, 1);
        $currentPage = min($currentPage, $totalPages);
        $currentRow = ($currentPage - 1) * $perPage;
        $this->setLimit($currentRow, $perPage);
        if ($this->sql === '') {
            $this->setSql("SELECT " . $this->getTableName() . ".* FROM " . $this->getTableName());
        }
        $statement = $this->executeQuery();
        $data = $statement->fetchAll();

        if ($data) {
            $this->arrayToObjects($data);
        }
        return [
            'data'        => $this->collection ?? [],
            'totalRows'   => $totalRows,
            'perPage'     => $perPage,
            'currentPage' => $currentPage,
            'totalPages'  => $totalPages,
            'hasNext'     => $currentPage < $totalPages,
            'hasPrev'     => $currentPage > 1,
        ];
    }

    protected function createMethod($values): Model
    {
        $values = $this->arrayToCastEncodeValue($values);
        $this->arrayToAttributes($values, $this);
        return $this->saveMethod();
    }

    protected function updateMethod($values): Model
    {
        $values = $this->arrayToCastEncodeValue($values);
        $this->arrayToAttributes($values, $this);
        return $this->saveMethod();
    }
}
