<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\ModelFactory;

use App\Dataset\Descriptor;
use App\Models\Job as JobData;

/**
 * Interface ModelFactoryInterface
 *
 * @package App\Dataset\Parser\Data
 */
interface ModelFactoryInterface
{

    /**
     * Set the job data object
     *
     * @param \App\Models\Job $jobData
     * @return \App\Dataset\ModelFactory\ModelFactoryInterface
     */
    public function setJobData(JobData $jobData);

    /**
     * Set a data descriptor object
     *
     * @param Descriptor $descriptor
     * @return \App\Dataset\ModelFactory\ModelFactoryInterface
     */
    public function setDescriptor(Descriptor $descriptor);

}