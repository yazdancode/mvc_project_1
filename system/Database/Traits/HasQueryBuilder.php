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

    public function resetWhere():void
    {
        $this->where = [];
    }

    public function setOrderBy($name , $expression):void
    {
        $this->orderby[] = $name . ' ' . $expression;
    }

    public function resetOrderBy():void
    {
        $this->orderby = [];
    }


}
