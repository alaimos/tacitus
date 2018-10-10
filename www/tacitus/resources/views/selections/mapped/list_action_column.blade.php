@if($mappedSelection->canDownload())
    <a href="{{ route('mapped-selections-download', ['selection' => $mappedSelection, 'type' => 'metadata']) }}"
       class="btn btn-xs btn-primary">
        <i class="fa fa-download" aria-hidden="true"></i> Download Metadata
    </a>
    <a href="{{ route('mapped-selections-download', ['selection' => $mappedSelection, 'type' => 'data']) }}"
       class="btn btn-xs btn-primary">
        <i class="fa fa-download" aria-hidden="true"></i> Download Data
    </a>
@endif
<a href="#"
   class="btn btn-xs btn-primary">
    <i class="fa fa-upload" aria-hidden="true"></i> Upload to Galaxy
</a>
@if($mappedSelection->canDelete())
    <a href="{{ route('mapped-selections-delete', ['selection' => $mappedSelection]) }}" class="btn btn-xs btn-danger">
        <i class="fa fa-trash" aria-hidden="true"></i> Delete
    </a>
@endif