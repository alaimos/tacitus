@if($platform->canDelete())
    <a href="{{ route('platforms-delete', ['platform' => $platform]) }}" class="btn btn-xs btn-danger">
        <i class="fa fa-trash" aria-hidden="true"></i> Delete
    </a>
@endif