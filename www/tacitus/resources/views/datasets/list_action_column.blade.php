@if($dataset->canSelect())
    <a href="{{ route('datasets-select', ['dataset' => $dataset]) }}" class="btn btn-xs btn-primary">
        <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Make Selection
    </a>
@endif
@if($dataset->canDelete())
    <a href="{{ route('datasets-delete', ['dataset' => $dataset]) }}" class="btn btn-xs btn-danger">
        <i class="fa fa-trash" aria-hidden="true"></i> Delete
    </a>
@endif