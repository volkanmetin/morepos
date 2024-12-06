<?php

namespace App\Datatable;

use App\Models\Warehouse;
use App\Support\Datatable\CustomColumn;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Services\DataTable;

class WarehouseDatatable extends DataTable
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
            ->rawColumns(['id', 'name', 'address', 'actions'])
            ->setRowId('id');
    }

    public function query(Warehouse $model): QueryBuilder
    {
        return $model->newQuery()
            ->select(['id', 'name', 'address'])
            //->filter($this->request()->all())
            ->withCount([
                'stocks',
            ]);
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
                ->addClass('fw-semibold')
                ->editable()
                ->searchable()
                ->orderable(),

            CustomColumn::make('address')
                ->title(__('Address'))
                ->editable()
                ->searchable()
                ->orderable(),

            CustomColumn::make('stocks_count')
                ->title(__('Stocks'))
                ->width(200)
                ->addClass('text-center')
                ->searchable(false)
                ->orderable(),

            CustomColumn::make('actions')
                ->title(__('global.actions'))
                ->width(100)
                ->orderable(false)
                ->searchable(false),
        ];
    }
}
