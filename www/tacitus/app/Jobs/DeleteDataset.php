<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Jobs;

use App\Jobs\Exception\JobException;
use App\Models\Dataset;
use App\Models\Job as JobData;
use App\Models\SampleSelection;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class DeleteDataset extends Job implements ShouldQueue
{

    use InteractsWithQueue, SerializesModels;

    /**
     * The model which holds all job information
     *
     * @var \App\Models\Job
     */
    protected $jobData;

    /**
     * ImportDataset constructor.
     *
     * @param \App\Models\Job $jobData
     */
    public function __construct(JobData $jobData)
    {
        $this->jobData = $jobData;
        if ($this->jobData->job_type != 'delete_dataset') {
            throw new JobException('This job cannot be run by this class.');
        }
        $this->onQueue('maintenance'); // Set the default queue for this job
    }

    /**
     * Print a log message
     *
     * @param string $message
     *
     * @return $this
     */
    protected function log($message)
    {
        $this->jobData->log = $this->jobData->log . $message;
        $this->jobData->save();
        return $this;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = $this->jobData->user;
        if ($this->attempts() > 1) {
            $this->delete();
        } else {
            $this->jobData->status = JobData::PROCESSING;
            $this->jobData->save();
            $this->sendNotification($user, 'comment',
                'One of your jobs (id: ' . $this->jobData->id . ') started processing.');
            $dataset = Dataset::whereId($this->jobData->job_data['dataset_id'])->first();
            if ($dataset === null) {
                $ok = false;
                $this->log("Unable to complete the job. The specified dataset was not found.\n");
            } else {
                try {
                    $this->log('Deleting dataset "' . $dataset->title . "\".\n");
                    $dataset->status = Dataset::PENDING;
                    $dataset->save();
                    $this->log('Deleting all samples');
                    $dataset->deleteSamples();
                    $this->log("...OK\n");
                    $this->log('Deleting all probes');
                    $dataset->deleteProbes();
                    $this->log("...OK\n");
                    $this->log('Deleting dataset record');
                    $dataset->realDelete();
                    $this->log("...OK\n");
                    $this->log("Dataset deleted successfully!\n");
                    $ok = true;
                } catch (\Exception $e) {
                    $this->log("\n");
                    $errorClass = join('', array_slice(explode('\\', get_class($e)), -1));
                    $this->log('Unable to complete job. Error "' . $errorClass . '" with message "' . $e->getMessage()
                               . "\".\n");
                    $ok = false;
                }
            }
            if ($ok) {
                $this->sendNotification($user, 'check-circle',
                    'One of your jobs (id: ' . $this->jobData->id . ') has been processed successfully.');
                $this->sendEmail($user, 'TACITuS Notification - A Job has been completed', 'emails.job_completed');
                $this->jobData->status = JobData::COMPLETED;
                $this->jobData->save();
            } else {
                $this->sendNotification($user, 'exclamation-triangle',
                    'One of your jobs (id: ' . $this->jobData->id . ') failed processing. Please check the ' .
                    'error log, correct the errors and submit a new request. Contact us ' .
                    'if you believe a bug is present in our system.');
                $this->sendEmail($user, 'TACITuS Notification - A Job Failed', 'emails.job_failed');
                $this->jobData->status = JobData::FAILED;
                $this->jobData->save();
            }
            $this->delete();
        }
    }

    /**
     * Delete the job
     *
     * @return void
     */
    public function destroy()
    {
        if (isset($this->jobData->job_data['selection_id'])) {
            $selection = SampleSelection::whereId($this->jobData->job_data['selection_id'])->first();
            if ($selection !== null) {
                $selection->delete();
            }
        }
        $this->jobData->deleteJobDirectory();
    }


}