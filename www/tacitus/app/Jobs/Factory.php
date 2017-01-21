<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Jobs;

use App\Jobs\Exception\JobException;
use App\Models\Job as JobData;

/**
 * Class Factory
 *
 * @package App\Jobs
 */
class Factory
{

    /**
     * Get an handler for this job
     *
     * @param \App\Models\Job $jobData
     *
     * @return \App\Jobs\Job
     */
    public static function getQueueJob(JobData $jobData)
    {
        $jobClass = '\App\Jobs\\' . studly_case($jobData->job_type);
        if (!class_exists($jobClass)) {
            throw new JobException('Unable to find a job class suitable for this job.');
        }
        return new $jobClass($jobData);
    }

}