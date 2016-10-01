@if(false) {{-- $platform->canUse()) --}}
    <a href="{{ route('platforms-view', ['platform' => $platform]) }}" class="btn btn-xs btn-primary">
        <i class="fa fa-eye" aria-hidden="true"></i> View
    </a>
@endif
@if($platform->canDelete())
    <a href="{{ route('platforms-delete', ['platform' => $platform]) }}" class="btn btn-xs btn-danger">
        <i class="fa fa-trash" aria-hidden="true"></i> Delete
    </a>
@endif