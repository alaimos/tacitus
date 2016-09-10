<?php

namespace App\Console;

use App\Tasks\AbstractTask;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\Notification::class,
        Commands\TestImport::class,
        Commands\TestSelection::class,
    ];

    /**
     * A list of TACITUS tasks
     *
     * @var array
     */
    protected $tasks = [
        \App\Tasks\CleanUpTask::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        foreach ($this->tasks as $taskClass) {
            /** @var AbstractTask $task */
            $task = new $taskClass;
            $event = $schedule->call(function () use ($taskClass) {
                /** @var AbstractTask $task */
                $task = new $taskClass;
                $task->run();
            });
            $task->schedule($event);
        }
    }
}
