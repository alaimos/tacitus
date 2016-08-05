<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Writer;

use App\Dataset\UseModelFactoryTrait;
use App\Dataset\Writer\Exception\DatasetWriterException;

/**
 * Class AbstractDatasetWriter
 *
 * @package App\Dataset\Writer
 */
abstract class AbstractDatasetWriter implements DatasetWriterInterface
{

    use UseModelFactoryTrait;

    /**
     * @var \App\Models\Dataset|null
     */
    protected $dataset = null;

    /**
     * @var \App\Models\Sample|null
     */
    protected $currentSample = null;

    /**
     * Create and store a dataset in the database
     *
     * @return \App\Models\Dataset
     * @throws \App\Dataset\Writer\Exception\DatasetWriterException
     */
    public function writeDataset()
    {
        if ($this->dataset === null) {
            try {
                $this->dataset = $this->modelFactory->getDataset();
                $this->dataset->save();
            } catch (\Exception $e) {
                throw new DatasetWriterException($e->getMessage(), 0, $e);
            }
        }
        return $this->dataset;
    }

}