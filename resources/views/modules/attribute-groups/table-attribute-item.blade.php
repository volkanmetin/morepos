<span class="badge bg-primary-subtle fs-xs my-1 attribute-item-text">
    @if ($model->color_code)
        <span style="display:inline-block; width:8px; height:8px; margin-right:3px; background-color:{{ $model->color_code }};"></span>
    @endif
    {{ $model->value }}
</span>
