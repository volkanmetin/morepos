<?php

namespace App\Http\Controllers;

use App\Datatable\CustomerDatatable;
use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(CustomerDatatable $datatable): Response
    {
        return inertia('Customer/Index', [
            'columns' => $datatable->html()->getColumns(),
        ]);
    }

    public function getData(CustomerDatatable $datatable, Request $request)
    {
        return $datatable->ajax();
    }

    public function show($id)
    {
        return Customer::findOrFail($id);
    }

    public function store(Request $request)
    {
        Customer::create($request->validate([
            'name' => 'required',
            'email' => 'nullable|email',
            'phone' => 'nullable',
            'address' => 'nullable',
        ]));
    }

    public function update(Request $request)
    {
        Customer::find(request('id'))->update($request->validate([
            'name' => 'required',
            'address' => 'required',
        ]));
    }

    public function destroy()
    {
        Customer::destroy(request('id'));
    }
}
