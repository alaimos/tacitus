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
use App\Models\Dataset;
use App\Models\Job as JobData;
use App\Models\Metadata;
use App\Models\MetadataIndex;
use App\Models\Probe;
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
     * Create a new Probe model
     *
     * @param string $name
     * @param array  $data
     * @return \App\Models\Probe
     */
    public function getProbe($name, $data)
    {
        $dataset = $this->getDataset();
        /** @var \App\Models\Probe $probe */
        $probe = Probe::whereName($name)->where('dataset_id', '=', $dataset->id)->first();
        if ($probe !== null) {
            $tmp = $probe->data;
            foreach ($data as $d) {
                $tmp[] = $d;
            }
            $probe->data = $tmp;
            $probe->save();
        } else {
            $probe = new Probe(['name' => $name, 'data' => $data]);
            $probe->dataset()->associate($dataset);
        }
        return $probe;
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
        $sample = new Sample(['name' => $name, 'sdata' => []]);
        $sample->dataset()->associate($this->getDataset());
        return $sample;
    }


}