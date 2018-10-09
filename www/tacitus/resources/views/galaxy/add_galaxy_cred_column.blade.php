<a href="{{route('edit-credential', ['credential' => $credential])}}" class="btn btn-xs btn-primary galaxy-edit-button">
    <i class="fa fa-edit" aria-hidden="true"></i> Edit
</a>
<a href="{{route('credential-delete', ['credential' => $credential])}}" class="btn btn-xs btn-danger">
    <i class="fa fa-trash" aria-hidden="true"></i> Delete
</a>