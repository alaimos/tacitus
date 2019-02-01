<div style="line-height: 2;">
    <a href="{{ route('mapped-selections-submit', ['selection' => $selection]) }}"
       class="btn btn-xs btn-primary">
        <i class="fa fa-globe" aria-hidden="true"></i> Map Identifiers
    </a>
    @if($selection->canDownload())
        <a href="{{ route('selections-download', ['selection' => $selection, 'type' => 'metadata']) }}"
           class="btn btn-xs btn-primary download-button">
            <i class="fa fa-download" aria-hidden="true"></i> Download Metadata
        </a>
        <a href="{{ route('selections-download', ['selection' => $selection, 'type' => 'data']) }}"
           class="btn btn-xs btn-primary download-button">
            <i class="fa fa-download" aria-hidden="true"></i> Download Data
        </a>
    @endif

    <a href="{{route('selection-upload', ['selection' => $selection]) }}"
       class="btn btn-xs btn-primary upload-selectionGalaxy-button">
        <i class="fa fa-upload" aria-hidden="true"></i> Upload to Galaxy
    </a>

    @if($selection->canDelete())
        <a href="{{ route('selections-delete', ['selection' => $selection]) }}" class="btn btn-xs btn-danger">
            <i class="fa fa-trash" aria-hidden="true"></i> Delete
        </a>
    @endif
</div>