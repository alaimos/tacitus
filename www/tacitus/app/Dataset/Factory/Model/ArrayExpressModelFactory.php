<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Factory\Model;

use App\Dataset\Factory\AbstractModelFactory;
use App\Models\Data;
use App\Models\Dataset;
use App\Models\Job as JobData;
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
        $descriptors = $this->descriptor->getDescriptors();
        $dataset = new Dataset([
            'original_id' => $descriptors['id'],
            'source_id'   => $this->source->id,
            'user_id'     => $this->jobData->job_data['user_id'],
            'title'       => $descriptors['name'],
            'private'     => $this->jobData->job_data['private'],
            'status'      => Dataset::PENDING
        ]);
        return $dataset;
    }
}