<button class="btn btn-sm btn-primary edit-btn" data-id="{{ $model->id }}" title="{{ __('global.edit') }}">
    <span class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
    <span class="button-text"><i class="fa fa-edit"></i></span>
</button>
<button class="btn btn-sm btn-danger delete-btn" data-id="{{ $model->id }}" title="{{ __('global.delete') }}">
    <span class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
    <span class="button-text"><i class="fa fa-times"></i></span>
</button>
