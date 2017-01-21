<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Factory\Model;

use App\Dataset\Factory\AbstractModelFactory;
use App\Dataset\Factory\Exception\FactoryException;
use App\Models\Dataset;
use App\Models\Probe;
use App\Models\Source;

/**
 * Class GeoGSEModelFactory
 *
 * @package App\Dataset\ModelFactory
 */
class GeoGSEModelFactory extends AbstractModelFactory
{

    /**
     * @var \App\Models\Source
     */
    protected $source;

    /**
     * @var \App\Models\Dataset
     */
    protected $dataset = null;

    /**
     * ArrayExpressModelFactory constructor.
     */
    public function __construct()
    {
        $this->source = Source::whereName('geogse')->first();
    }

    /**
     * Get a Dataset object associated with the current descriptor.
     * If no Dataset object is available, it will be instantiated.
     *
     * @param array $options
     *
     * @return Dataset
     */
    public function getDataset(array $options = [])
    {
        if ($this->dataset === null) {
            $descriptors = $this->descriptor->getDescriptors();
            $dataset = Dataset::whereOriginalId($descriptors['id'])->whereSourceId($this->source->id)->first();
            if ($dataset !== null && $dataset instanceof Dataset) {
                if (!$dataset->private
                    || $dataset->user_id == $this->jobData->user->id
                    || $dataset->user->can('use-all-datasets')
                ) {
                    if ($dataset->status == Dataset::READY) {
                        throw new FactoryException('A dataset has already been created from the same source.');
                    } elseif ($dataset->status == Dataset::PENDING) {
                        throw new FactoryException('A dataset from the same source is being parsed.');
                    } elseif ($dataset->status == Dataset::FAILED) {
                        $dataset->status = Dataset::PENDING;
                        return $dataset;
                    }
                }
            }
            $dataset = new Dataset([
                'original_id' => $descriptors['id'],
                'source_id'   => $this->source->id,
                'user_id'     => $this->jobData->user->id,
                'title'       => $descriptors['title'],
                'private'     => $this->jobData->job_data['private'],
                'status'      => Dataset::PENDING,
                'platform_id' => $descriptors['platform_id'],
            ]);
            $this->dataset = $dataset;
        }
        return $this->dataset;
    }

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
            foreach ($data as $key => $d) {
                $tmp[$key] = $d;
            }
            $probe->data = $tmp;
            $probe->save();
        } else {
            $probe = new Probe(['name' => $name, 'data' => $data]);
            $probe->dataset()->associate($dataset);
        }
        return $probe;
    }
}