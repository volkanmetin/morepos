<?php

namespace App\Datatable;

use App\Models\Category;
use App\Support\Datatable\CustomColumn;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Services\DataTable;

class CategoryDatatable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('id', function ($model) {
                return $model->id;
            })
            ->editColumn('name', function ($model) {
                return $model->name;
            })
            ->editColumn('parent_id', function ($model) {
                return $model->parent?->name ?? '-';
            })
            ->addColumn('actions', function ($model) {
                return view('partials.table-action', compact('model'))->render();
            })
            ->rawColumns(['id', 'name', 'parent_id', 'actions'])
            ->setRowId('id');
    }

    public function query(Category $model): QueryBuilder
    {
        return $model->newQuery()
            ->select(['id', 'name', 'parent_id'])
            ->with('parent');
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

            CustomColumn::make('parent_id')
                ->title(__('Parent'))
                ->addClass('fw-semibold')
                ->editable()
                ->searchable()
                ->inputType('select')
                ->options(Category::all()->pluck('name', 'id')->toArray()),

            CustomColumn::make('name')
                ->title(__('Name'))
                ->addClass('fw-semibold')
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
