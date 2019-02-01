<div style="line-height: 2;">
    @if($integration->canDownload())
        <a href="{{ route('integration-download', ['integration' => $integration, 'type' => 'metadata']) }}"
           class="btn btn-xs btn-primary download-button">
            <i class="fa fa-download" aria-hidden="true"></i> Download Metadata
        </a>
        <a href="{{ route('integration-download', ['integration' => $integration, 'type' => 'data']) }}"
           class="btn btn-xs btn-primary download-button">
            <i class="fa fa-download" aria-hidden="true"></i> Download Data
        </a>
    @endif

    <a href="{{route('integration-upload', ['integration' => $integration])}}"
       class="btn btn-xs btn-primary upload-selectionGalaxy-button">
        <i class="fa fa-upload" aria-hidden="true"></i> Upload to Galaxy
    </a>

    @if($integration->canDelete())
        <a href="{{ route('integration-delete', ['integration' => $integration]) }}" class="btn btn-xs btn-danger">
            <i class="fa fa-trash" aria-hidden="true"></i> Delete
        </a>
    @endif
</div>