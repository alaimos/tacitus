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

class Factory
{

    public static function getQueueJob(JobData $jobData)
    {
        $jobClass = '\App\Jobs\\' . studly_case($jobData->job_type);
        if (!class_exists($jobClass)) {
            throw new JobException('Unable to find a job class suitable for this job.');
        }
        return new $jobClass($jobData);
    }

}