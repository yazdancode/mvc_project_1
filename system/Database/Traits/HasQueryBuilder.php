<?php

namespace System\Database\Traits;
use System\Database\DBConnection\DBConnection;

trait HasQueryBuilder
{
    private string $sql = '';
    protected array $where = [];
    protected array $orderby = [];

    private array $limit =[];

    private array $values =[];

    private array $bindValues =[];

    protected function setSql($query):void
    {
        $this->sql =$query;
    }
    protected function getSql():string
    {
        return $this->sql;
    }
    protected function resetSql():void
    {
        $this->sql ='';
    }

    protected function setWhere($operator, $condition):void
    {
        $array = ['operator'=>$operator, 'condition'=>$condition];
        $this->where[] = $array;
    }

    protected function resetWhere():void
    {
        $this->where = [];
    }

    protected function setOrderBy($name , $expression):void
    {
        $this->orderby[] = $name . ' ' . $expression;
    }

    protected function resetOrderBy():void
    {
        $this->orderby = [];
    }

    protected function setLimit($from, $number):void
    {
        $this->limit['from']=(int) $from;
        $this->limit['number']=(int) $number;
    }

    protected function resetLimit():void
    {
        unset($this->limit['from'], $this->limit['number']);
    }

    protected function addValue($attribute, $value):void
    {
        $this->values[$attribute] = $value;
        $this->bindValues[] = $value;
    }

    protected function removeValues():void
    {
        $this->values = [];
        $this->bindValues = [];
    }

    protected function resetQuery():void
    {
        $this->resetSql();
        $this->resetWhere();
        $this->resetOrderBy();
        $this->resetLimit();
        $this->removeValues();
    }

    protected function executeQuery():void
    {
        $query = $this->sql;
        if (!empty($this->where)) {
            $whereString = '';
            foreach ($this->where as $index => $where) {
                if ($index === 0) {
                    $whereString .= $where['condition'];
                } else {
                    $whereString .= ' ' . $where['operator'] . ' ' . $where['condition'];
                }
            }
            $query .= ' WHERE ' . $whereString;
        }
        if(!empty($this->orderby))
        {
            $query .= ' ORDER BY '. implode(',', $this->orderby);
        }
        if(!empty($this->limit))
        {
            $query .= ' LIMIT ' . $this->limit['from'] . ', ' . $this->limit['number'] . ' ';
        }
        $query .= ' ;';
        echo $query. '<hr>/';
    }
}
