<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Writer;

/**
 * Class DefaultDatasetWriter
 *
 * @package App\Dataset\Writer
 */
class DefaultDatasetWriter extends AbstractDatasetWriter
{

    /**
     * Write a sample object
     *
     * @param mixed $data
     * @return \App\Models\Sample
     */
    public function writeSample($data)
    {
        $this->currentSample = $this->modelFactory->getSample($data['name']);
        $this->currentSample->save();
        return $this->currentSample;
    }

    /**
     * Write a Data object
     *
     * @param mixed $data
     * @return \App\Models\Data
     */
    public function writeData($data)
    {
        $sample = $this->getSample($data);
        $dataModel = $this->modelFactory->getData($data['probe'], $data['value'], $sample);
        $dataModel->save();
        return $dataModel;
    }

    /**
     * Write a Metadata object
     *
     * @param mixed $data
     * @return \App\Models\Metadata
     */
    public function writeMetadata($data)
    {
        $sample = $this->getSample($data);
        $metaModel = $this->modelFactory->getMetadata($data['name'], $data['value'], $sample);
        $metaModel->save();
        return $metaModel;
    }

    /**
     * Write a MetadataIndex object
     *
     * @param mixed $data
     * @return \App\Models\MetadataIndex
     */
    public function writeMetadataIndex($data)
    {
        $indexModel = $this->modelFactory->getMetadataIndex($data['name']);
        $indexModel->save();
        return $indexModel;
    }

}