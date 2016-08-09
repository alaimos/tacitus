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
        if ($this->attempts() > 3) {
            $this->jobData->status = JobData::FAILED;
            $this->jobData->save();
            $this->delete();
        } else {
            $registry = new ParserFactoryRegistry();
            $factories = $registry->getParsers($this->jobData->job_data['source_type']);
            if (empty($factories)) {
                throw new JobException('Unable to find parsers suited for this import job.');
            }
            $this->jobData->status = JobData::PROCESSING;
            $this->jobData->save();
            $ok = false;
            foreach ($factories as $factory) {
                $job = $factory->setJobData($this->jobData)->getRealImporter();
                if ($job->run()) {
                    $ok = true;
                    break;
                }
            }
            if ($ok) {
                $this->jobData->status = JobData::COMPLETED;
            } else {
                $this->jobData->status = JobData::FAILED;
            }
        }
        $this->jobData->save();
    }

}