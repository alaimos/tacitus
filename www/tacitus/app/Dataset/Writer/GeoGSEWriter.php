<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Writer;

use App\Dataset\Writer\Exception\DatasetWriterException;

/**
 * Class GeoGSEWriter
 *
 * @package App\Dataset\Writer
 */
class GeoGSEWriter extends DefaultDatasetWriter
{

    /**
     * Write a sample object
     *
     * @param mixed $data
     *
     * @return \App\Models\Sample|boolean
     */
    public function writeSample($data)
    {
        if ($this->is2DArray($data)) {
            throw new DatasetWriterException('Bulk insertion is not supported for samples.');
        } else {
            $this->currentSample = $this->modelFactory->getSample($data['name']);
            $this->currentSample->save();
            $this->sampleRegistry->register($this->currentSample, $data['position']);
            return $this->currentSample;
        }
    }

}