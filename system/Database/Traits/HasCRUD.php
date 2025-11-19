<?php

namespace System\Database\Traits;

use System\Database\DBConnection\DBConnection;
use PDO;

trait HasCRUD
{
    /* ============================================================
     | -------------  Core Attribute Helpers  ---------------------
     ============================================================ */

    protected function addCastValue(string $attribute, $value): void
    {
        if ($this->inCastsAttributes($attribute)) {
            $value = $this->castEncodeValue($attribute, $value);
        }
        $this->addValue($attribute, $value);
    }

    protected function attributeIsFillable(string $attribute): bool
    {
        return in_array($attribute, $this->fillable)
            && property_exists($this, $attribute)
            && $this->$attribute !== null;
    }

    protected function buildSetString(array $attributes): string
    {
        $pairs = [];

        foreach ($attributes as $name => $value) {
            $pairs[] = "{$this->getAttributeName($name)} = ?";
            $this->addCastValue($name, $value);
        }

        return implode(', ', $pairs);
    }

    /* ============================================================
     | ----------------------  Create  ----------------------------
     ============================================================ */

    protected function createMethod(array $input)
    {
        $values = $this->arrayToCastEncodeValue($input);
        $this->arrayToAttributes($values, $this);

        return $this->saveMethod();
    }

    /* ============================================================
     | ----------------------  Save (Insert / Update) -------------
     ============================================================ */

    protected function saveMethod(): self
    {
        $fillableValues = [];

        foreach ($this->fillable as $attribute) {
            if ($this->attributeIsFillable($attribute)) {
                $fillableValues[$attribute] = $this->$attribute;
            }
        }

        $setString = $this->buildSetString($fillableValues);

        /* INSERT */
        if (!isset($this->{$this->primaryKey})) {
            $sql = "INSERT INTO {$this->getTableName()} 
                    SET $setString, `{$this->createdAt}` = NOW()";

            $this->setSql($sql);
        }
        /* UPDATE */ else {
            $sql = "UPDATE {$this->getTableName()} 
                    SET $setString, `{$this->updatedAt}` = NOW()";
            $this->setSql($sql);
            $this->setWhere("AND", "{$this->primaryKey} = ?");
            $this->addValue($this->primaryKey, $this->{$this->primaryKey});
        }

        $this->executeQuery();
        $this->resetQuery();

        // Insert → reload object with fresh DB values
        if (!isset($this->{$this->primaryKey})) {
            $lastId = DBConnection::getInstance()->lastInsertId();
            $fresh = $this->findMethod($lastId);

            if ($fresh) {
                foreach (get_object_vars($fresh) as $key => $value) {
                    $this->$key = $value;
                }
            }
        }

        $this-> setAllowedMethods(['update', 'delete', 'save']);
        return $this;
    }

    /* ============================================================
     | ----------------------  Update  ----------------------------
     ============================================================ */

    protected function updateMethod(array $input)
    {
        $values = $this->arrayToCastEncodeValue($input);
        $this->arrayToAttributes($values, $this);
        return $this->saveMethod();
    }


    /* ============================================================
     | ----------------------  Delete  ----------------------------
     ============================================================ */

    protected function deleteMethod($id = null): bool
    {
        $obj = $this;

        if ($id !== null) {
            $obj = $this->findMethod($id);
            $this->resetQuery();
        }

        if (!$obj) {
            return false;
        }

        $obj->setSql("DELETE FROM {$obj->getTableName()}");
        $obj->setWhere("AND", "{$this->primaryKey} = ?");
        $obj->addValue($this->primaryKey, $obj->{$this->primaryKey});

        return (bool) $obj->executeQuery();
    }


    /* ============================================================
     | ----------------------  Read  ------------------------------
     ============================================================ */

    protected function findMethod($id): ?self
    {
        $this->setSql("SELECT * FROM {$this->getTableName()}
                        WHERE {$this->primaryKey} = ? LIMIT 1");

        $this->addValue($this->primaryKey, $id);

        $statement = $this->executeQuery();
        $data = $statement->fetch(PDO::FETCH_ASSOC);

        $this->setAllowedMethods(['update', 'delete', 'save']);

        if ($data) {
            $this->arrayToAttributes($data);
            return $this;
        }
        return null;
    }

    protected function allMethod(): array
    {
        $this->setSql("SELECT * FROM {$this->getTableName()}");

        $statement = $this->executeQuery();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        if ($data) {
            $this->arrayToObjects($data);
            return $this->collection;
        }

        return [];
    }


    protected function getMethod(array $fields = []): array
    {
        if ($this->sql === '') {

            $select = empty($fields)
                ? "{$this->getTableName()}.*"
                : implode(',', array_map(fn ($f) => $this->getAttributeName($f), $fields));

            $this->setSql("SELECT $select FROM {$this->getTableName()}");
        }

        $stmt = $this->executeQuery();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($data) {
            $this->arrayToObjects($data);
            return $this->collection;
        }

        return [];
    }


    /* ============================================================
     | ----------------------  Where Conditions -------------------
     ============================================================ */

    protected function baseWhere(string $operator, string $attribute, $value, ?string $sign = '='): self
    {
        $condition = "{$this->getAttributeName($attribute)} $sign ?";
        $this->setWhere($operator, $condition);
        $this->addCastValue($attribute, $value);

        $this->setAllowedMethods([
            'where', 'whereOr', 'whereIn', 'whereNull', 'whereNotNull',
            'limit', 'orderBy', 'get', 'paginate'
        ]);

        return $this;
    }

    protected function whereMethod($attribute, $value): self
    {
        return $this->baseWhere("AND", $attribute, $value);
    }

    protected function whereOrMethod($attribute, $value): self
    {
        return $this->baseWhere("OR", $attribute, $value);
    }

    protected function whereNullMethod($attribute): self
    {
        $this->setWhere("AND", "{$this->getAttributeName($attribute)} IS NULL");

        $this->setAllowedMethods([
            'where', 'whereOr', 'whereIn', 'whereNull', 'whereNotNull',
            'limit', 'orderBy', 'get', 'paginate'
        ]);
        return $this;
    }

    protected function whereNotNullMethod($attribute): self
    {
        $this->setWhere("AND", "{$this->getAttributeName($attribute)} IS NOT NULL");

        $this->setAllowedMethods([
            'where', 'whereOr', 'whereIn', 'whereNull', 'whereNotNull',
            'limit', 'orderBy', 'get', 'paginate'
        ]);
        return $this;
    }

    protected function whereInMethod(string $attribute, array $values): self
    {
        if (empty($values)) {
            return $this;
        }

        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $condition = "{$this->getAttributeName($attribute)} IN ($placeholders)";

        $this->setWhere("AND", $condition);

        foreach ($values as $v) {
            $this->addCastValue($attribute, $v);
        }

        $this->setAllowedMethods([
            'where', 'whereOr', 'whereIn', 'whereNull', 'whereNotNull',
            'limit', 'orderBy', 'get', 'paginate'
        ]);

        return $this;
    }

    /* ============================================================
     | --------------------- Order / Limit ------------------------
     ============================================================ */

    protected function orderByMethod($attribute, $direction): self
    {
        $this->setOrderBy($attribute, strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC');
        $this->setAllowedMethods(['limit', 'orderBy', 'get', 'paginate']);
        return $this;
    }

    protected function limitMethod($offset, $number): self
    {
        $this->setLimit((int)$offset, (int)$number);
        $this->setAllowedMethods(['limit', 'get', 'paginate']);
        return $this;
    }


    /* ============================================================
     | ---------------------- Paginate ----------------------------
     ============================================================ */

    protected function paginateMethod(int $perPage): array
    {
        $totalRows = $this->getCount();

        $currentPage = $_GET['page'] ?? 1;
        $currentPage = max((int)$currentPage, 1);

        $totalPages = max((int)ceil($totalRows / $perPage), 1);
        $currentPage = min($currentPage, $totalPages);

        $offset = ($currentPage - 1) * $perPage;

        $this->setLimit($offset, $perPage);

        if ($this->sql === '') {
            $this->setSql("SELECT {$this->getTableName()}.* FROM {$this->getTableName()}");
        }

        $stmt = $this->executeQuery();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
}
