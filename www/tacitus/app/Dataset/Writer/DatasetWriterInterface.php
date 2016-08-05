<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Writer;

use App\Dataset\ModelFactoryAwareInterface;

/**
 * Interface DatasetWriterInterface
 *
 * @package App\Dataset\ModelFactory
 */
interface DatasetWriterInterface extends ModelFactoryAwareInterface
{

    /**
     * Create and store a dataset in the database
     *
     * @return \App\Models\Dataset
     * @throws \App\Dataset\Writer\Exception\DatasetWriterException
     */
    public function writeDataset();

    /**
     * Create and store something in the database
     *
     * @param string $type
     * @param mixed  $data
     * @return object
     * @throws \App\Dataset\Writer\Exception\DatasetWriterException
     */
    public function write($type, $data);


}