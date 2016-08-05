<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset;

use App\Models\Job as JobData;

/**
 * Interface JobDataAwareInterface
 *
 * @package App\Dataset
 */
interface JobDataAwareInterface
{

    /**
     * Set the job data object
     *
     * @param \App\Models\Job $jobData
     *
     * @return $this
     */
    public function setJobData(JobData $jobData);

    /**
     * Get the job data object
     *
     * @return JobData
     */
    public function getJobData();

}