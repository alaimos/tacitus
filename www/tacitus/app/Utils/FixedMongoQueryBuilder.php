<?php

namespace App\Utils;

use Closure;
use DateTime;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Jenssegers\Mongodb\Connection;
use Jenssegers\Mongodb\Query\Builder;
use MongoDB\BSON\ObjectID;
use MongoDB\BSON\Regex;
use MongoDB\BSON\UTCDateTime;

class FixedMongoQueryBuilder extends Builder
{

    /**
     * Create a new query builder instance.
     *
     * @param Builder $builder
     */
    public function __construct(Builder $builder)
    {
        $this->grammar = $builder->grammar;
        $this->connection = $builder->connection;
        $this->collection = $builder->collection;
        $this->processor = $builder->processor;
        $this->paginating = $builder->paginating;
        $this->projections = $builder->projections;
        $this->hint = $builder->hint;
        $this->timeout = $builder->timeout;
        $this->bindings = $builder->bindings;
        $this->aggregate = $builder->aggregate;
        $this->columns = $builder->columns;
        $this->distinct = $builder->distinct;
        $this->from = $builder->from;
        $this->joins = $builder->joins;
        $this->wheres = $builder->wheres;
        $this->groups = $builder->groups;
        $this->orders = $builder->orders;
        $this->limit = (int)$builder->limit;
        $this->offset = (int)$builder->offset;
        $this->unions = $builder->unions;
        $this->unionLimit = $builder->unionLimit;
        $this->unionOffset = $builder->unionOffset;
        $this->unionOrders = $builder->unionOrders;
        $this->lock = $builder->lock;
        $this->backups = $builder->backups;
        $this->bindingBackups = $builder->bindingBackups;
    }

    /**
     * Set the "limit" value of the query.
     *
     * @param  int $value
     * @return $this
     */
    public function limit($value)
    {
        $value = (int)$value;
        return parent::limit($value);
    }

    /**
     * Set the "offset" value of the query.
     *
     * @param  int $value
     * @return $this
     */
    public function offset($value)
    {
        $value = (int)$value;
        return parent::offset($value);
    }


}
