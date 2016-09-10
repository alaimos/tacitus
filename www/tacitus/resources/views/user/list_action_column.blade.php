<a href="{{ route('user::profile', ['user' => $user]) }}" class="btn btn-xs btn-primary">
    <i class="fa fa-eye" aria-hidden="true"></i> View Profile
</a>
@if ($user->id != Auth::user()->id)
    <a href="{{ route('user::delete', ['user' => $user]) }}" class="btn btn-xs btn-danger">
        <i class="fa fa-trash" aria-hidden="true"></i> Delete
    </a>
@endif