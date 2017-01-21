<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Factory;

use App\Dataset\Traits\InteractsWithDescriptor;
use App\Dataset\Traits\InteractsWithJobData;
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

    use InteractsWithJobData, InteractsWithDescriptor;

    /**
     * Create a new Probe model
     *
     * @param string $name
     * @param array  $data
     * @param array  $options
     *
     * @return Probe
     */
    public function getProbe($name, $data, array $options = [])
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
     * @param array              $options
     *
     * @return Metadata
     */
    public function getMetadata($name, $value, Sample $sample, array $options = [])
    {
        $metadata = new Metadata(['name' => $name, 'value' => $value]);
        $metadata->sample()->associate($sample);
        return $metadata;
    }

    /**
     * Create a new Metadata Index Model
     *
     * @param string $name
     * @param array  $options
     *
     * @return MetadataIndex
     */
    public function getMetadataIndex($name, array $options = [])
    {
        $index = new MetadataIndex(['name' => $name]);
        $index->dataset()->associate($this->getDataset());
        return $index;
    }

    /**
     * Create a new Sample Model
     *
     * @param string $name
     * @param array  $options
     *
     * @return Sample
     */
    public function getSample($name, array $options = [])
    {
        $sample = new Sample(['name' => $name]);
        $sample->dataset()->associate($this->getDataset());
        return $sample;
    }


}