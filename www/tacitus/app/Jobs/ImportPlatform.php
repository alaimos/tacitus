<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Jobs;

use App\Jobs\Exception\JobException;
use App\Models\Platform;
use App\Platform\Import\Factory\PlatformImportFactory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Job as JobData;

class ImportPlatform extends Job implements ShouldQueue
{

    use InteractsWithQueue, SerializesModels;

    /**
     * The model which holds all job information
     *
     * @var \App\Models\Job
     */
    protected $jobData;

    /**
     * Log callback
     *
     * @var callable
     */
    protected $logCallback = null;

    /**
     * ImportDataset constructor.
     *
     * @param \App\Models\Job $jobData
     */
    public function __construct(JobData $jobData)
    {
        $this->jobData = $jobData;
        if ($this->jobData->job_type != 'import_platform') {
            throw new JobException('This job cannot be run by this class.');
        }
        $this->onQueue('importer'); // Set the default queue for this job
    }

    /**
     * Get a log callback
     *
     * @return callable
     */
    public function getLogCallback()
    {
        if ($this->logCallback === null) {
            $this->logCallback = function ($message, $autoCommit = false) {
                $this->jobData->log = $this->jobData->log . $message;
                if ($autoCommit) {
                    $this->jobData->save();
                }
            };
        }
        return $this->logCallback;
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
            $factory = new PlatformImportFactory();
            $importerType = $this->jobData->job_data['importer_type'];
            $importerConfig = $this->jobData->job_data['importer_config'];
            $this->jobData->status = JobData::PROCESSING;
            $this->jobData->save();
            $importer = null;
            try {
                $importerConfig['logCallback'] = $this->getLogCallback();
                $importerConfig['user'] = $user;
                $this->sendNotification($user, 'comment',
                    'One of your jobs (id: ' . $this->jobData->id . ') started processing.');
                $importer = $factory->getImporter($importerType, $importerConfig);
                $platform = $importer->import()->getPlatform();
                if ($platform !== null) {
                    $platform->status = Platform::READY;
                    $platform->save();
                }
                $this->sendNotification($user, 'check-circle',
                    'One of your jobs (id: ' . $this->jobData->id . ') has been processed successfully.');
                $this->sendEmail($user, 'TACITuS Notification - A Job has been completed', 'emails.job_completed');
                $this->jobData->status = JobData::COMPLETED;
                $this->jobData->deleteJobDirectory();
                $this->jobData->save();
            } catch (\Exception $e) {
                if ($importer !== null) {
                    $platform = $importer->getPlatform();
                    if ($platform !== null) {
                        $platform->delete();
                    }
                }
                $errorClass = join('', array_slice(explode('\\', get_class($e)), -1));
                $this->jobData->log = $this->jobData->log . "\n\n" . 'Unable to complete job. Error "' . $errorClass .
                                      '" with message "' . $e->getMessage() . "\".\n";
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