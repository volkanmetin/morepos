<?php

namespace App\Http\Controllers;

use App\Datatable\WarehouseDatatable;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Response;

class WarehouseController extends Controller
{
    public function index(WarehouseDatatable $datatable): Response
    {
        return inertia('Warehouse/Index', [
            'columns' => $datatable->html()->getColumns(),
        ]);
    }

    public function getData(WarehouseDatatable $datatable, Request $request)
    {
        return $datatable->ajax();
    }

    public function show($id)
    {
        return Warehouse::find($id);
    }

    public function store(Request $request)
    {
        Warehouse::create($request->validate([
            'name' => 'required',
            'address' => 'required',
        ]));
    }

    public function update(Request $request)
    {
        Warehouse::find(request('id'))->update($request->validate([
            'name' => 'required',
            'address' => 'required',
        ]));
    }

    public function destroy()
    {
        Warehouse::destroy(request('id'));
    }
}
