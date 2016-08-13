@if ($jobData->status != \App\Models\Job::QUEUED)
    <a href="Javascript:;" data-id="{{$jobData->id}}" class="btn btn-xs btn-primary btn-view-job">
        <i class="fa fa-eye" aria-hidden="true"></i> View log
    </a>
@endif
@if ($jobData->status != \App\Models\Job::PROCESSING)
    <a href="{{ route('jobs-delete', ['job' => $jobData]) }}" class="btn btn-xs btn-danger">
        <i class="fa fa-trash" aria-hidden="true"></i> Delete
    </a>
@endif