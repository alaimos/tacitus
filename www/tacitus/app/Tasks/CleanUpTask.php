<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Tasks;

use App\Models\Dataset;
use App\Models\Job as JobData;
use App\Models\SampleSelection;
use App\Models\Task;
use Carbon\Carbon;
use DaveJamesMiller\Breadcrumbs\Exception;
use Illuminate\Console\Scheduling\Event;


class CleanUpTask extends AbstractTask
{

    const OLD_JOB_OFFSET = 30;
    const OLD_SELECTION_OFFSET = 7;
    const OLD_DATASET_OFFSET = 365;
    const OLD_TASKS_OFFSET = 2;

    /**
     * Returns the description of this task
     *
     * @return string
     */
    protected function description()
    {
        return 'System daily cleanup';
    }

    /**
     * Cleanup old tasks
     *
     * @return $this
     */
    public function cleanUpOldTasks()
    {
        $this->log("Cleaning up old tasks...");
        $oldest = Carbon::now()->subDays(self::OLD_TASKS_OFFSET);
        $count = 0;
        foreach (Task::whereIn('status', [Task::FAILED, Task::COMPLETED])->get() as $task) {
            /** @var Task $task */
            if ($task->created_at->lt($oldest)) {
                $task->delete();
                $count++;
            }
        }
        $this->log("OK! (Deleted $count tasks)\n");
        return $this;
    }

    /**
     * Cleanup old jobs
     *
     * @return $this
     */
    public function cleanUpOldJobs()
    {
        $this->log("Cleaning up old jobs...");
        $oldest = Carbon::now()->subDays(self::OLD_JOB_OFFSET);
        $count = 0;
        foreach (JobData::whereIn('status', [JobData::FAILED, JobData::COMPLETED])->get() as $jobData) {
            /** @var JobData $jobData */
            if ($jobData->created_at->lt($oldest)) {
                $jobData->deleteJobDirectory();
                $jobData->delete();
                $count++;
            }
        }
        $this->log("OK! (Deleted $count jobs)\n");
        return $this;
    }

    /**
     * Cleanup old selections
     *
     * @return $this
     */
    public function cleanUpOldSelections()
    {
        $this->log("Cleaning up old selections...");
        $oldest = Carbon::now()->subDays(self::OLD_SELECTION_OFFSET);
        $count = 0;
        $query = SampleSelection::whereIn('status', [SampleSelection::FAILED, SampleSelection::READY]);
        foreach ($query->get() as $selection) {
            /** @var SampleSelection $selection */
            if ($selection->created_at->lt($oldest)) {
                $selection->delete();
                $count++;
            }
        }
        $this->log("OK! (Deleted $count selections)\n");
        return $this;
    }

    /**
     * Cleanup old datasets
     *
     * @return $this
     */
    public function cleanUpOldDatasets()
    {
        $this->log("Cleaning up old datasets...");
        $oldest = Carbon::now()->subDays(self::OLD_DATASET_OFFSET);
        $count = 0;
        $query = Dataset::whereIn('status', [Dataset::FAILED, Dataset::READY]);
        foreach ($query->get() as $dataset) {
            /** @var Dataset $dataset */
            if ($dataset->created_at->lt($oldest)) {
                $dataset->deleteSamples();
                $dataset->deleteProbes();
                $dataset->realDelete();
                $count++;
            }
        }
        $this->log("OK! (Deleted $count datasets)\n");
        return $this;
    }

    /**
     * Runs the task
     *
     * @return $this
     */
    public function run()
    {
        $this->before();
        $failed = false;
        try {
            $this->log("Starting Clean Up Task\n")
                ->cleanUpOldSelections()
                ->cleanUpOldDatasets()
                ->cleanUpOldJobs()
                ->cleanUpOldTasks()
                ->log("DONE!\n");
        } catch (Exception $e) {
            $this->log("\nFAILED: An error occurred!!\n\n" . $e->__toString());
            $failed = true;
        }
        $this->after($failed);
        return $this;
    }

    /**
     * Schedule this command
     *
     * @param \Illuminate\Console\Scheduling\Event $event
     * @return $this
     */
    public function schedule(Event $event)
    {
        $event->daily();
        return $this;
    }
}