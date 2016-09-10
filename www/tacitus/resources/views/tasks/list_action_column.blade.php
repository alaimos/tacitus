<a href="Javascript:;" data-id="{{$task->id}}" class="btn btn-xs btn-primary btn-view-task">
    <i class="fa fa-eye" aria-hidden="true"></i> View log
</a>
@if ($task->status != \App\Models\Task::RUNNING)
    <a href="{{ route('tasks-delete', ['task' => $task]) }}" class="btn btn-xs btn-danger">
        <i class="fa fa-trash" aria-hidden="true"></i> Delete
    </a>
@endif