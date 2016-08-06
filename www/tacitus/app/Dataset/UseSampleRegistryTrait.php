<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset;

use App\Dataset\Registry\SampleRegistry;

/**
 * Class UseSampleRegistryTrait
 *
 * @package App\Dataset
 */
trait UseSampleRegistryTrait
{

    /**
     * Sample Registry
     *
     * @var \App\Dataset\Registry\SampleRegistry
     */
    protected $sampleRegistry;

    /**
     * Set a sample registry object
     *
     * @param \App\Dataset\Registry\SampleRegistry $sampleRegistry
     * @return $this
     */
    public function setSampleRegistry(SampleRegistry $sampleRegistry)
    {
        $this->sampleRegistry = $sampleRegistry;
        return $this;
    }

    /**
     * Get the sample registry object
     *
     * @return \App\Dataset\Registry\SampleRegistry
     */
    public function getModelFactory()
    {
        return $this->sampleRegistry;
    }

}