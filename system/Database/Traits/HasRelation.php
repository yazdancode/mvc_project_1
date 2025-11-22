<?php

namespace System\Database\Traits;

trait HasRelation
{
    protected function hasOne($model, $foreignKey, $localKey)
    {
        if ($this->{$this->primaryKey}) {
            return (new $model())->getHasOneRelation(
                $this->table,
                $foreignKey,
                $localKey,
                $this->$localKey
            );
        }
        return null;
    }

    public function getHasOneRelation($parentTable, $foreignKey, $otherKey, $otherKeyValue)
    {
        $this->setSql("
            SELECT b.* 
            FROM `$parentTable` AS a 
            JOIN `{$this->getTableName()}` AS b 
            ON a.`$otherKey` = b.`$foreignKey`
        ");
        $this->setWhere('AND', "a.`$otherKey` = ? ");
        $this->addValue($otherKey, $otherKeyValue);

        $statement = $this->executeQuery();
        $data = $statement->fetch();

        if ($data) {
            return $this->arrayToAttributes($data);
        }
        return null;
    }
}
