<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Writer;

use App\Dataset\Descriptor;
use App\Models\Job as JobData;

/**
 * Interface DatasetWriterInterface
 *
 * @package App\Dataset\ModelFactory
 */
interface DatasetWriterInterface
{

    /**
     * Set the logger callback
     *
     * @param callable $callback
     * @return \App\Dataset\Writer\DatasetWriterInterface
     */
    public function setLogCallback(callable $callback);

    /**
     * Set the job data object
     *
     * @param \App\Models\Job $jobData
     * @return \App\Dataset\Writer\DatasetWriterInterface
     */
    public function setJobData(JobData $jobData);

    /**
     * Set a data descriptor object
     *
     * @param Descriptor $descriptor
     * @return \App\Dataset\Writer\DatasetWriterInterface
     */
    public function setDescriptor(Descriptor $descriptor);

}