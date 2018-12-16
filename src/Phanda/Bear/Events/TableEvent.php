<?php

namespace Phanda\Bear\Events;

use Phanda\Bear\Table\Table;
use Phanda\Events\Event;

class TableEvent extends Event
{
    /**
     * @var Table
     */
    protected $table;

    /**
     * @var array|null
     */
    protected $data;

    /**
     * TableEvent constructor.
     *
     * @param Table $table
     * @param array|null $data
     */
    public function __construct(Table $table, ?array $data = null)
    {
        $this->table = $table;
        $this->data = $data;
    }

    /**
     * @return Table
     */
    public function getTable(): Table
    {
        return $this->table;
    }

    /**
     * @return array|null
     */
    public function getData(): ?array
    {
        return $this->data;
    }

}