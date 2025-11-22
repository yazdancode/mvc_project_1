<?php

namespace System\Database\Traits;

trait HasSoftDelete
{
    use HasQueryBuilder;
    use HasCRUD;
    use HasAttributes;
    use HasMethodCaller;
    protected function deleteMethod($id = null)
    {
        $object = $this;

        if ($id) {
            $this->resetQuery();
            $object = $this->findMethod($id);
        }

        if ($object) {
            $object->resetQuery();
            $object->setSql(
                "UPDATE " . $object->getTableName() .
                " SET " . $this->getAttributeName($this->deletedAt) . " = NOW()"
            );
            $object->setWhere("AND", $this->getAttributeName($this->primaryKey) . " = ?");
            $object->addValue($object->primaryKey, $object->{$object->primaryKey});
            return $object->executeQuery();
        }

        return false;
    }

    protected function allMethod(): array
    {
        $this->setSql("SELECT ".$this->getTableName().".* FROM ".$this->getTableName());
        $this->setWhere("AND", $this->getAttributeName($this->deletedAt)." IS NULL ");
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
        $this->resetQuery();
        $this->setSql("SELECT ".$this->getTableName().".* FROM ".$this->getTableName());
        $this->setWhere("AND", $this->getAttributeName($this->primaryKey)." = ?");
        $this->addValue($this->primaryKey, $id);
        $this->setWhere("AND", $this->getAttributeName($this->deletedAt)." IS NULL ");
        $statement = $this->executeQuery();
        $data = $statement->fetch();

        $this->setAllowedMethods(['update', 'delete', 'save']);

        if ($data) {
            $this->arrayToAttributes($data);
            return $this;
        }
        return null;
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
        $this->setWhere("AND", $this->getAttributeName($this->deletedAt)." IS NULL");

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
        $this->setWhere("AND", $this->getAttributeName($this->deletedAt)." IS NULL");
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
}
