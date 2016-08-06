<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Factory;

use App\Dataset\Descriptor;
use App\Dataset\UseDescriptorTrait;
use App\Dataset\UseJobDataTrait;
use App\Models\Data;
use App\Models\Job as JobData;
use App\Models\Metadata;
use App\Models\MetadataIndex;
use App\Models\Sample;

/**
 * Class AbstractModelFactory
 *
 * @package App\Dataset\ModelFactory
 */
abstract class AbstractModelFactory implements ModelFactoryInterface
{

    use UseJobDataTrait, UseDescriptorTrait;

    /**
     * Create a new Data model
     *
     * @param string             $probe
     * @param string             $value
     * @param \App\Models\Sample $sample
     *
     * @return \App\Models\Data
     */
    public function getData($probe, $value, Sample $sample)
    {
        $data = new Data(['probe' => $probe, 'value' => $value]);
        $data->sample()->associate($sample);
        return $data;
    }

    /**
     * Create a new Metadata Model
     *
     * @param string             $name
     * @param string             $value
     * @param \App\Models\Sample $sample
     *
     * @return \App\Models\Metadata
     */
    public function getMetadata($name, $value, Sample $sample)
    {
        $metadata = new Metadata(['name' => $name, 'value' => $value]);
        $metadata->sample()->associate($sample);
        return $metadata;
    }

    /**
     * Create a new Metadata Index Model
     *
     * @param string $name
     *
     * @return \App\Models\MetadataIndex
     */
    public function getMetadataIndex($name)
    {
        $index = new MetadataIndex(['name' => $name]);
        $index->dataset()->associate($this->getDataset());
        return $index;
    }

    /**
     * Create a new Sample Model
     *
     * @param string $name
     *
     * @return \App\Models\Sample
     */
    public function getSample($name)
    {
        $sample = new Sample(['name' => $name]);
        $sample->dataset()->associate($this->getDataset());
        return $sample;
    }


}