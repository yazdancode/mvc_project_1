<?php

namespace System\Database\ORM;

use System\Database\Traits\HasAttributes;
use System\Database\Traits\HasCRUD;
use System\Database\Traits\HasMethodCaller;
use System\Database\Traits\HasQueryBuilder;
use System\Database\Traits\HasRelation;
use System\Database\Traits\HasSoftDelete;

abstract class Model
{
    use HasAttributes;
    use HasCRUD;
    use HasMethodCaller;
    use HasQueryBuilder;
    use HasRelation;
    use HasSoftDelete;

    protected string $table;
    protected array $fillable = [];
    protected array $hidden = [];
    protected array $casts = [];
    protected string $primaryKey = 'id';
    protected string $createdAt = 'created_at';
    protected string $updatedAt = 'updated_at';
    protected ?string $deletedAt = null;
    protected array $collection = [];
}
