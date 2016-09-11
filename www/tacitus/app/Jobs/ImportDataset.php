<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Jobs;

use App\Dataset\Registry\ParserFactoryRegistry;
use App\Jobs\Exception\JobException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Job as JobData;

class ImportDataset extends Job implements ShouldQueue
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
        if ($this->jobData->job_type != 'import_dataset') {
            throw new JobException('This job cannot be run by this class.');
        }
        $this->onQueue('importer'); // Set the default queue for this job
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = $this->jobData->user;
        if ($this->attempts() > 3) {
            $this->jobData->status = JobData::FAILED;
            $this->jobData->save();
            $this->sendNotification($user, 'exclamation-triangle',
                'One of your jobs (id: ' . $this->jobData->id . ') failed processing. ' .
                'The job has been dropped from the processing queue. Please check the ' .
                'error log, correct the errors and submit a new request. Contact us ' .
                'if you believe a bug is present in our system.');
            $this->sendEmail($user, 'TACITuS Notification - A Job Failed', 'emails.job_failed', ['retry' => false]);
            $this->delete();
        } else {
            $registry = new ParserFactoryRegistry();
            $factories = $registry->getParsers($this->jobData->job_data['source_type']);
            if (empty($factories)) {
                throw new JobException('Unable to find parsers suited for this import job.');
            }
            $this->jobData->status = JobData::PROCESSING;
            $this->jobData->save();
            $this->sendNotification($user, 'comment',
                'One of your jobs (id: ' . $this->jobData->id . ') started processing.');
            $ok = false;
            foreach ($factories as $factory) {
                $job = $factory->setJobData($this->jobData)->getRealImporter();
                if ($job->run()) {
                    $ok = true;
                    break;
                }
            }
            if ($ok) {
                $this->sendNotification($user, 'check-circle',
                    'One of your jobs (id: ' . $this->jobData->id . ') has been processed successfully.');
                $this->sendEmail($user, 'TACITuS Notification - A Job has been completed', 'emails.job_completed');
                $this->jobData->status = JobData::COMPLETED;
                $this->jobData->deleteJobDirectory();
                $this->jobData->save();
            } else {
                $this->sendNotification($user, 'exclamation-triangle',
                    'One of your jobs (id: ' . $this->jobData->id . ') failed processing. Our system will automatically retry the job in order to check for temporary errors.');
                $this->sendEmail($user, 'TACITuS Notification - A Job Failed', 'emails.job_failed', ['retry' => true]);
                $this->jobData->status = JobData::FAILED;
                $this->jobData->save();
                throw new JobException('Job Failed'); //Releases the job back into the queue
            }
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