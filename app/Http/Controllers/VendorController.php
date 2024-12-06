<?php

namespace App\Http\Controllers;

use App\Datatable\VendorDatatable;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(VendorDatatable $datatable)
    {
        return inertia('Vendor/Index', [
            'columns' => $datatable->html()->getColumns(),
        ]);
    }

    public function getData(VendorDatatable $datatable, Request $request)
    {
        return $datatable->ajax();
    }

    public function show($id)
    {
        return Vendor::find($id);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
        ]);

        Vendor::create($validated);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
        ]);

        Vendor::find(request('id'))->update($validated);
    }

    public function destroy()
    {
        Vendor::destroy(request('id'));
    }
}
