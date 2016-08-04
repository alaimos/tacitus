<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Parser\Data;

use App\Dataset\Descriptor;
use App\Models\Job as JobData;

/**
 * Interface DataParserInterface
 *
 * @package App\Dataset\Parser\Data
 */
interface DataParserInterface
{

    /**
     * Set the logger callback
     *
     * @param callable $callback
     * @return \App\Dataset\Parser\Data\DataParserInterface
     */
    public function setLogCallback(callable $callback);

    /**
     * Set the job data object
     *
     * @param \App\Models\Job $jobData
     * @return \App\Dataset\Parser\Data\DataParserInterface
     */
    public function setJobData(JobData $jobData);

    /**
     * Set a data descriptor object
     *
     * @param Descriptor $descriptor
     * @return \App\Dataset\Parser\Data\DataParserInterface
     */
    public function setDescriptor(Descriptor $descriptor);


}