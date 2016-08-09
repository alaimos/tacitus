<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Writer;

use App\Dataset\Contracts\ModelFactoryAwareInterface;
use App\Dataset\Contracts\SampleRegistryAwareInterface;

/**
 * Interface DatasetWriterInterface
 *
 * @package App\Dataset\ModelFactory
 */
interface DatasetWriterInterface extends ModelFactoryAwareInterface, SampleRegistryAwareInterface
{

    /**
     * Create and store a dataset in the database
     *
     * @return \App\Models\Dataset
     * @throws \App\Dataset\Writer\Exception\DatasetWriterException
     */
    public function writeDataset();

    /**
     * Create and store something in the database.
     * Returns a model object if only one element has to be written. Otherwise it will return a boolean indicating
     * whether the operation was successful or not.
     *
     * @param string $type
     * @param mixed  $data
     *
     * @return object|boolean
     * @throws \App\Dataset\Writer\Exception\DatasetWriterException
     */
    public function write($type, $data);


}