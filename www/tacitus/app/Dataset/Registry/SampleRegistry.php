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
     * A map from sample position to sample id
     *
     * @var array
     */
    protected $indexByPosition = [];

    /**
     * Register a sample
     *
     * @param Sample       $sample
     * @param null|integer $position
     *
     * @return SampleRegistry
     */
    public function register(Sample $sample, $position = null)
    {
        $key = $sample->getKey();
        $this->samples[$key] = $sample;
        $this->indexByName[$sample->name] = $key;
        if ($position !== null) {
            $this->indexByPosition[$position] = $key;
            $sample->position = $position;
            $sample->save();
        } else {
            $this->indexByPosition[] = $key;
            $sample->position = count($this->indexByPosition) - 1;
            $sample->save();
        }
        return $this;
    }

    /**
     * Get a sample by its identifier
     *
     * @param integer $sampleId
     *
     * @return \App\Models\Sample|null
     */
    public function get($sampleId)
    {
        return (isset($this->samples[$sampleId])) ? $this->samples[$sampleId] : null;
    }

    /**
     * Get a sample by its name
     *
     * @param string $sampleName
     *
     * @return \App\Models\Sample|null
     */
    public function getByName($sampleName)
    {
        return (isset($this->indexByName[$sampleName])) ? $this->get($this->indexByName[$sampleName]) : null;
    }

    /**
     * Get a sample by its position
     *
     * @param integer $position
     *
     * @return \App\Models\Sample|null
     */
    public function getByPosition($position)
    {
        return (isset($this->indexByPosition[$position])) ? $this->get($this->indexByPosition[$position]) : null;
    }

}