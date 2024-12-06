<?php

namespace App\Datatable;

use App\Models\Customer;
use App\Support\Datatable\CustomColumn;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Services\DataTable;

class CustomerDatatable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('id', function ($model) {
                return $model->id;
            })
            ->addColumn('actions', function ($model) {
                return view('partials.table-action', compact('model'))->render();
            })
            ->rawColumns(['id', 'name', 'email', 'phone', 'address', 'actions'])
            ->setRowId('id');
    }

    public function query(Customer $model): QueryBuilder
    {
        return $model->newQuery()
            ->select(['id', 'name', 'email', 'phone', 'address']);
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()->columns($this->getColumns());
    }

    public function getColumns(): array
    {
        return [
            CustomColumn::make('id')
                ->title(__('ID'))
                ->width(100)
                ->searchable()
                ->orderable(),

            CustomColumn::make('name')
                ->title(__('Name'))
                ->required()
                ->addClass('fw-semibold')
                ->editable()
                ->searchable()
                ->orderable(),

            CustomColumn::make('email')
                ->title(__('Email'))
                ->editable()
                ->searchable()
                ->orderable(false),

            CustomColumn::make('phone')
                ->title(__('Phone'))
                ->addClass('text-start')
                ->editable()
                ->searchable()
                ->orderable(false),

            CustomColumn::make('address')
                ->title(__('Address'))
                ->editable()
                ->searchable()
                ->orderable(),

            CustomColumn::make('actions')
                ->title(__('global.actions'))
                ->width(100)
                ->orderable(false)
                ->searchable(false),
        ];
    }
}
