<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset;

use App\Models\Job as JobData;

/**
 * Class UseJobData
 *
 * @package App\Dataset
 */
trait UseJobDataTrait
{

    /**
     * @var \App\Models\Job
     */
    protected $jobData;

    /**
     * Set the job data object
     *
     * @param \App\Models\Job $jobData
     *
     * @return $this
     */
    public function setJobData(JobData $jobData)
    {
        $this->jobData = $jobData;
        return $this;
    }

    /**
     * Get the job data object
     *
     * @return JobData
     */
    public function getJobData()
    {
        return $this->jobData;
    }


}