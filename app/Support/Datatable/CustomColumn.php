<?php

namespace App\Support\Datatable;

use Yajra\DataTables\Html\Column;

class CustomColumn extends Column
{
    public function editable(bool $flag = true): static
    {
        $this->attributes['editable'] = $flag;

        return $this;
    }

    public function options(array $options = []): static
    {
        $this->attributes['options'] = $options;

        return $this;
    }

    public function draggable(bool $flag = true): static
    {
        $this->attributes['draggable'] = $flag;

        return $this;
    }

    public function relation(array $relationColumns = []): static
    {
        $this->attributes['hasRelation'] = true;
        $this->attributes['relationColumns'] = $relationColumns;

        return $this;
    }

    public function required(bool $flag = true): static
    {
        $this->attributes['required'] = $flag;

        return $this;
    }
}
