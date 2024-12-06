<?php

namespace App\Http\Controllers;

use App\Datatable\BrandDatatable;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Response;

class BrandController extends Controller
{
    public function index(BrandDatatable $datatable): Response
    {
        return inertia('Brand/Index', [
            'columns' => $datatable->html()->getColumns(),
        ]);
    }

    public function getData(BrandDatatable $datatable, Request $request)
    {
        return $datatable->ajax();
    }

    public function show($id)
    {
        return Brand::find($id);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        Brand::create($validated);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
        ]);

        Brand::find(request('id'))->update($validated);
    }

    public function destroy()
    {
        Brand::destroy(request('id'));
    }
}
