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
use App\Models\Source;

/**
 * Class AbstractModelFactory
 *
 * @package App\Dataset\ModelFactory
 */
class ArrayExpressModelFactory extends AbstractModelFactory
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
        $this->source = Source::whereName('arrexp')->first();
    }

    /**
     * Get a Dataset object associated with the current descriptor.
     * If no Dataset object is available, it will be instantiated.
     *
     * @return \App\Models\Dataset
     */
    public function getDataset()
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
                'title'       => $descriptors['name'],
                'private'     => $this->jobData->job_data['private'],
                'status'      => Dataset::PENDING
            ]);
            $this->dataset = $dataset;
        }
        return $this->dataset;
    }
}