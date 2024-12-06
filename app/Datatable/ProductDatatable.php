<?php

namespace App\Datatable;

use App\Models\Category;
use App\Models\Product;
use App\Support\Datatable\CustomColumn;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Services\DataTable;

class ProductDatatable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('id', function ($model) {
                return $model->id;
            })
            ->addColumn('image', function ($model) {
                return '<div class="avatar-sm bg-light rounded p-1"><img src="'.$model->image.'" alt="" class="img-fluid d-block show-image-modal" /></div>';
            })
            ->editColumn('name', function ($model) {
                return '<div class="fw-semibold"><a href="'.route('product.show', $model->id).'" class="text-dark">'.$model->name.'</a></div><div class="fs-sm mt-1"><span class="fw-medium">Kategori:</span> '.$model->category->name.'</div>';
            })
            ->addColumn('price', function ($model) {
                if ($model->discounted_price > 0) {
                    return '<span class="text-muted text-decoration-line-through">'.$model->sale_price.'</span><br /><span class="fw-semibold">'.$model->discounted_price.'</span>';
                }

                return '<span class="fw-semibold">'.$model->sale_price.'</span>';
            })
            ->editColumn('variants', function ($model) {
                return view('modules.product.table-variants', compact('model'))->render();
                //return $model->variants->pluck('attributeValues')->flatten()->pluck('attributeGroup')->unique('id')->pluck('name')->toArray();
            })
            ->addColumn('actions', function ($model) {
                return view('partials.table-action', compact('model'))->render();
            })
            ->rawColumns(['id', 'name', 'image', 'price', 'variants', 'actions'])
            ->setRowId('id');
    }

    public function query(Product $model): QueryBuilder
    {
        return $model->newQuery()
            ->select(['id', 'name', 'category_id', 'sale_price', 'purchase_price', 'discounted_price'])
            ->with([
                'category' => function ($query) {
                    $query->select('id', 'name');
                },
                'variants.attributeValues.attributeGroup' => function ($query) {
                    $query->select('id', 'name');
                },
                'media' => function ($query) {
                    $query->where('order_column', 1);
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
                ->width(70)
                ->searchable()
                ->orderable(),

            CustomColumn::make('image')
                ->title(__('Image'))
                ->width(100)
                ->searchable(false)
                ->orderable(false),

            CustomColumn::make('name')
                ->title(__('Name'))
                ->width(300)
                ->editable()
                ->searchable()
                ->orderable(),

            CustomColumn::make('price')
                ->title(__('Price'))
                ->addClass('text-end')
                ->width(100)
                ->searchable(false)
                ->orderable(),

            CustomColumn::make('category_id')
                ->title(__('category.item'))
                ->width(200)
                ->hidden()
                ->editable()
                ->sortable(false)
                ->searchable(false)
                ->inputType('select')
                ->options(Category::all()->pluck('name', 'id')->toArray()),
            /*
                ->relation([
                    [
                        'type' => 'select',
                        'key' => 'id',
                        'label' => __('Category'),
                        'options' => Category::all()->pluck('name', 'id'),
                    ],
                ]),
                */

            CustomColumn::make('variants')
                ->title(__('product.variants'))
                ->editable()
                ->sortable(false)
                ->searchable(false),
            /*
                ->draggable()
                ->relation([
                    [
                        'type' => 'text',
                        'key' => 'value',
                        'label' => __('attribute-group.value'),
                    ],
                    [
                        'type' => 'select',
                        'key' => 'deneme',
                        'label' => 'Deneme',
                        'options' => [
                            'asc' => __('asc'),
                            'desc' => __('desc'),
                        ],
                    ]
                ])
                    */

            CustomColumn::make('actions')
                ->title(__('global.actions'))
                ->width(100)
                ->orderable(false)
                ->searchable(false),
        ];
    }
}
