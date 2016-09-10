@if($selection->canDownload())
    <a href="{{ route('selections-download', ['selection' => $selection, 'type' => 'metadata']) }}"
       class="btn btn-xs btn-primary">
        <i class="fa fa-download" aria-hidden="true"></i> Download Metadata
    </a>
    <a href="{{ route('selections-download', ['selection' => $selection, 'type' => 'data']) }}"
       class="btn btn-xs btn-primary">
        <i class="fa fa-download" aria-hidden="true"></i> Download Data
    </a>
@endif
@if($selection->canDelete())
    <a href="{{ route('selections-delete', ['selection' => $selection]) }}" class="btn btn-xs btn-danger">
        <i class="fa fa-trash" aria-hidden="true"></i> Delete
    </a>
@endif