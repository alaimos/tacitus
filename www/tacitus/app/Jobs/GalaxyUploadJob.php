<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Jobs;

use App\Dataset\Uploader\GalaxyUploader;
use App\Jobs\Exception\JobException;
use App\Models\Job as JobData;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GalaxyUploadJob extends Job implements ShouldQueue
{

    use InteractsWithQueue, SerializesModels;

    /**
     * The model which holds all job information
     *
     * @var \App\Models\Job
     */
    protected $jobData;

    /**
     * @var integer
     */
    protected $prevPercentage;

    /**
     * ImportDataset constructor.
     *
     * @param \App\Models\Job $jobData
     */
    public function __construct(JobData $jobData)
    {
        $this->jobData = $jobData;
        if ($this->jobData->job_type != 'galaxy_upload_job') {
            throw new JobException('This job cannot be run by this class.');
        }
        $this->onQueue('analysis'); // Set the default queue for this job
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
     * Log progress percentage
     *
     * @param integer $current
     * @param integer $total
     *
     * @return void
     */
    protected function logProgress($current, $total)
    {
        $percentage = floor(min(100, ((float)$current / (float)$total) * 100));
        if (($percentage % 10) == 0 && $percentage != 100 && $percentage != $this->prevPercentage) {
            $this->log('...' . $percentage . '%');
        }
        $this->prevPercentage = $percentage;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
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
            $this->log("Upload to galaxy is starting.\n");
            $ok = true;
            try {
                $uploader = new GalaxyUploader();
                $uploader->setJobData($this->jobData)->setLogCallback(function ($message, $autoCommit = false) {
                    $this->log($message);
                });
                $uploader->upload();
                $this->log("Data uploaded to Galaxy!\n");
            } catch (\Exception $e) {
                $this->log("\n");
                $errorClass = join('', array_slice(explode('\\', get_class($e)), -1));
                $this->log('Unable to complete job. Error "' . $errorClass . '" with message "'
                           . $e->getMessage() . "\".\n");
                $ok = false;
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
        $this->jobData->deleteJobDirectory();
    }

}