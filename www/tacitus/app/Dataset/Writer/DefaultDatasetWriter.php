<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Writer;

use App\Dataset\Writer\Exception\DatasetWriterException;
use App\Models\Data;
use App\Models\Metadata;
use App\Models\MetadataIndex;

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
     * @return \App\Models\Sample|boolean
     */
    public function writeSample($data)
    {
        if ($this->is2DArray($data)) {
            throw new DatasetWriterException('Bulk insertion is not supported for samples.');
        }
        $this->currentSample = $this->modelFactory->getSample($data['name']);
        $this->currentSample->save();
        $this->sampleRegistry->register($this->currentSample);
        return $this->currentSample;
    }

    /**
     * Write a Data object
     *
     * @param mixed $data
     * @return \App\Models\Probe|boolean
     */
    public function writeData($data)
    {
        if ($this->is2DArray($data)) {
            foreach ($data as $probe) {
                if (is_array($probe) && isset($probe['name']) && isset($probe['data']) && is_array($probe['data'])) {
                    $this->writeData($probe);
                }
            }
            return true;
        }
        $probe = $this->modelFactory->getProbe($data['name'], $data['data']);
        $probe->save();
        return $probe;
    }

    /**
     * Write a Metadata object
     *
     * @param mixed $data
     * @return \App\Models\Metadata
     */
    public function writeMetadata($data)
    {
        if (!$this->is2DArray($data)) {
            $sample = $this->getSample($data);
            $metaModel = $this->modelFactory->getMetadata($data['name'], $data['value'], $sample);
            $metaModel->save();
            return $metaModel;
        } else {
            $data = array_map(function ($item) {
                $sample = $this->getSample($item);
                $this->removeSample($item);
                $item['sample_id'] = $sample->getKey();
                return $item;
            }, array_filter($data, function ($item) {
                return (is_array($item) && isset($item['name']) && isset($item['value'])
                        && $this->getSample($item) !== null);
            }));
            return with(new Metadata)->insertMany($data);
        }
    }

    /**
     * Write a MetadataIndex object
     *
     * @param mixed $data
     * @return \App\Models\MetadataIndex
     */
    public function writeMetadataIndex($data)
    {
        if (!$this->is2DArray($data)) {
            $indexModel = $this->modelFactory->getMetadataIndex($data['name']);
            $indexModel->save();
            return $indexModel;
        } else {
            $data = array_map(function ($item) {
                $this->removeSample($item);
                $item['dataset_id'] = $this->dataset->id;
                return $item;
            }, array_filter($data, function ($item) {
                return (is_array($item) && isset($item['name']));
            }));
            return with(new MetadataIndex)->insertMany($data);
        }
    }

}