<?php

namespace App\Datatable;

use App\Models\AttributeGroup;
use App\Support\Datatable\CustomColumn;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Services\DataTable;

class AttributeGroupDatatable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('id', function ($model) {
                return $model->id;
            })
            ->editColumn('values', function ($model) {
                return $model->values->map(function ($value) {
                    return view('modules.attribute-groups.table-attribute-item', ['model' => $value])->render();
                })->implode(' ');
            })
            ->addColumn('actions', function ($model) {
                return view('partials.table-action', compact('model'))->render();
            })
            ->rawColumns(['id', 'name', 'values', 'actions'])
            ->setRowId('id');
    }

    public function query(AttributeGroup $model): QueryBuilder
    {
        return $model->newQuery()
            ->select(['id', 'name', 'description'])
            ->with([
                'values' => function ($query) {
                    $query->select('id', 'attribute_group_id', 'value', 'color_code');
                },
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
                ->width(200)
                ->editable()
                ->searchable()
                ->orderable(),

            CustomColumn::make('values')
                ->title(__('attribute-group.values'))
                ->editable()
                ->sortable(false)
                ->draggable()
                ->relation([
                    [
                        'type' => 'text',
                        'key' => 'value',
                        'label' => __('attribute-group.value'),
                    ],
                    [
                        'type' => 'text',
                        'key' => 'color_code',
                        'label' => __('attribute-group.color_code'),
                    ],
                    /*
                    [
                        'type' => 'select',
                        'key' => 'deneme',
                        'label' => 'Deneme',
                        'options' => [
                            'asc' => __('asc'),
                            'desc' => __('desc'),
                        ],
                    ]
                    */
                ])
                ->searchable(),

            CustomColumn::make('actions')
                ->title(__('global.actions'))
                ->width(100)
                ->orderable(false)
                ->searchable(false),
        ];
    }
}
