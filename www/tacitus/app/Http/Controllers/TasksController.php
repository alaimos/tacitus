<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Utils\Permissions;
use Datatables;
use Flash;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;

class TasksController extends Controller
{

    /**
     * Registers routes handled by this controller
     *
     * @param \Illuminate\Routing\Router $router
     */
    public static function registerRoutes(Router $router)
    {
        $router->get('/tasks', ['as'         => 'tasks-list',
                                'uses'       => 'TasksController@tasksList',
                                'middleware' => ['permission:' . Permissions::ADMINISTER]]);
        $router->any('/tasks/data', ['as'         => 'tasks-lists-data',
                                     'uses'       => 'TasksController@tasksData',
                                     'middleware' => ['permission:' . Permissions::ADMINISTER]]);
        $router->any('/tasks/{task}/view', ['as'         => 'tasks-view',
                                            'uses'       => 'TasksController@viewTask',
                                            'middleware' => ['permission:' . Permissions::ADMINISTER]]);
        $router->get('/tasks/{task}/delete', ['as'         => 'tasks-delete',
                                              'uses'       => 'TasksController@delete',
                                              'middleware' => ['permission:' . Permissions::ADMINISTER]]);
    }

    /**
     * Prepare the list of tasks
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function tasksList(Request $request)
    {
        return view('tasks.list');
    }

    /**
     * Process datatables ajax request for the list of tasks.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function tasksData(Request $request)
    {
        /** @var \Yajra\Datatables\Engines\QueryBuilderEngine $table */
        $table = Datatables::of(Task::listTasks());
        $table->editColumn('status', function (Task $task) {
            $text = '';
            switch ($task->status) {
                case Task::RUNNING:
                    $text = '<i class="fa fa-spinner faa-spin animated" aria-hidden="true"></i> ';
                    break;
                case Task::FAILED:
                    $text = '<i class="fa fa-exclamation-circle" aria-hidden="true"></i> ';
                    break;
                case Task::COMPLETED:
                    $text = '<i class="fa fa-check-circle" aria-hidden="true"></i> ';
                    break;
            }
            return $text . ucfirst($task->status);
        })->addColumn('action', function (Task $task) {
            return view('tasks.list_action_column', [
                'task' => $task,
            ])->render();
        });
        return $table->make(true);
    }

    /**
     * Return a task data
     *
     * @param Request $request
     * @param Task    $task
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function viewTask(Request $request, Task $task)
    {
        return response()->json($task->toArray());
    }


    /**
     * Delete a task
     *
     * @param Task $task
     *
     * @return mixed
     */
    public function delete(Task $task)
    {
        if (!$task || !$task->exists) {
            abort(404, 'Unable to find the task.');
        }
        if (!$task->canDelete()) {
            abort(401, 'You are not allowed to delete this task.');
        }
        $task->delete();
        Flash::success('Task deleted successfully.');
        return back();
    }
}
