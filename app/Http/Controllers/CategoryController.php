<?php

namespace App\Http\Controllers;

use App\Datatable\CategoryDatatable;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(CategoryDatatable $datatable)
    {
        return inertia('Category/Index', [
            'columns' => $datatable->html()->getColumns(),
        ]);
    }

    public function getData(CategoryDatatable $datatable, Request $request)
    {
        return $datatable->ajax();
    }

    public function list()
    {
        return inertia('Category/List', [
            'categories' => Category::whereNull('parent_id')->with('children')->get(),
        ]);
    }

    public function show($id)
    {
        return Category::find($id);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
        ]);

        Category::create($validated);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
        ]);

        Category::find(request('id'))->update($validated);
    }

    public function destroy()
    {
        Category::destroy(request('id'));
    }
}
