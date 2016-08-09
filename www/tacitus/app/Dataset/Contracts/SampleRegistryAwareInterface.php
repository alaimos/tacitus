<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Contracts;

use App\Dataset\Registry\SampleRegistry;


/**
 * Interface SampleRegistryAwareInterface
 *
 * @package App\Dataset
 */
interface SampleRegistryAwareInterface
{

    /**
     * Set a sample registry object
     *
     * @param \App\Dataset\Registry\SampleRegistry $sampleRegistry
     * @return $this
     */
    public function setSampleRegistry(SampleRegistry $sampleRegistry);

    /**
     * Get the sample registry object
     *
     * @return \App\Dataset\Registry\SampleRegistry
     */
    public function getModelFactory();

}