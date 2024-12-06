<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-3 fs-xs">
    @foreach ($model->variants as $variant)
        <div class="col">
            <div class="card mb-0">
                <div class="card-body px-1 py-0">
                    @foreach ($variant->attributeValues as $attributeValue)
                        @include('modules.attribute-groups.table-attribute-item', ['model' => $attributeValue])
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>
