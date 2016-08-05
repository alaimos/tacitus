<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Registry;

use App\Models\Sample;

class SampleRegistry
{

    /**
     * A set of samples indexed by identifier
     *
     * @var \App\Models\Sample[]
     */
    protected $samples = [];

    /**
     * A map from sample name to sample id
     *
     * @var array
     */
    protected $indexByName = [];

    /**
     * Register a sample
     *
     * @param Sample $sample
     * @return \App\Dataset\Registry\SampleRegistry
     */
    public function register(Sample $sample)
    {
        $key = $sample->getKey();
        $this->samples[$key] = $sample;
        $this->indexByName[$sample->name] = $key;
        return $this;
    }

    /**
     * Get a sample by its identifier
     *
     * @param integer $sampleId
     * @return Sample|null
     */
    public function get($sampleId)
    {
        return (isset($this->samples[$sampleId])) ? $this->samples[$sampleId] : null;
    }

    /**
     * Get a sample by its name
     *
     * @param string $sampleName
     * @return Sample|null
     */
    public function getByName($sampleName)
    {
        return (isset($this->indexByName[$sampleName])) ? $this->get($this->indexByName[$sampleName]) : null;
    }

}