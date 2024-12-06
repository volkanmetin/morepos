<?php

namespace App\Http\Controllers;

use App\Datatable\AttributeGroupDatatable;
use App\Models\AttributeGroup;
use Illuminate\Http\Request;
use Inertia\Response;

class AttributeGroupController extends Controller
{
    public function index(AttributeGroupDatatable $datatable): Response
    {
        return inertia('AttributeGroup/Index', [
            'columns' => $datatable->html()->getColumns(),
        ]);
    }

    public function getData(AttributeGroupDatatable $datatable, Request $request)
    {
        return $datatable->ajax();
    }

    public function show($id)
    {
        return AttributeGroup::where('id', $id)->with('values')->first();
    }

    public function store(Request $request)
    {
        ray($request->all());

        $attributeGroup = AttributeGroup::create($request->validate([
            'name' => 'required',
            'description' => 'nullable',
        ]));

        if ($request->has('values')) {
            $requestValues = collect($request->values)->filter();
            foreach ($requestValues as $value) {
                $attributeGroup->values()->create(['value' => $value['value'], 'sort' => $value['sort'] ?? 0]);
            }
        }
    }

    public function update(Request $request)
    {
        ray($request->all());

        $attributeGroup = AttributeGroup::find(request('id'));
        $attributeGroup->update($request->validate([
            'name' => 'required',
            'description' => 'nullable',
        ]));

        if ($request->has('values')) {
            $requestValues = collect($request->values)->filter(fn ($value) => isset($value['value']) && ! empty($value['value']));
            $requestValueIds = $requestValues->pluck('id')->filter()->toArray();

            // Mevcut değerlerden, request'te olmayanları sil
            $attributeGroup->values()->whereNotIn('id', $requestValueIds)->delete();

            foreach ($requestValues as $value) {
                if (isset($value['id'])) {
                    $attributeGroup->values()->where('id', $value['id'])->update(['value' => $value['value'], 'sort' => $value['sort']]);
                } else {
                    $attributeGroup->values()->create(['value' => $value['value'], 'sort' => $value['sort'] ?? 0]);
                }
            }

            return $attributeGroup;

        }

        $attributeGroup->values()->delete();

        return $attributeGroup;
    }

    public function destroy()
    {
        $attributeGroup = AttributeGroup::find(request('id'));
        $attributeGroup->values()->delete();
        $attributeGroup->delete();
    }
}
