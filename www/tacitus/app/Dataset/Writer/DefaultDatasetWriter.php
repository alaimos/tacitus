<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Writer;

use App\Dataset\Descriptor;
use App\Dataset\Writer\Exception\DatasetWriterException;

/**
 * Class DefaultDatasetWriter
 *
 * @package App\Dataset\Writer
 */
class DefaultDatasetWriter extends AbstractDatasetWriter
{

    /**
     * Create and store something in the database
     *
     * @param string $type
     * @param mixed  $data
     * @return object
     * @throws \App\Dataset\Writer\Exception\DatasetWriterException
     */
    public function write($type, $data)
    {
        switch ($type) {
            case Descriptor::TYPE_SAMPLE:
                try {
                    $this->currentSample = $this->modelFactory->getSample($data['name']);
                    $this->currentSample->save();
                } catch (\Exception $e) {
                    throw new DatasetWriterException($e->getMessage(), 0, $e);
                }
                return $this->currentSample;
                break;
            case Descriptor::TYPE_DATA:
                try {
                    $sample = (isset($data['sample'])) ?: $this->currentSample;
                    $dataModel = $this->modelFactory->getData($data['probe'], $data['value'], $sample);
                    $dataModel->save();
                } catch (\Exception $e) {
                    throw new DatasetWriterException($e->getMessage(), 0, $e);
                }
                return $dataModel;
                break;
            case Descriptor::TYPE_METADATA:
                try {
                    $sample = (isset($data['sample'])) ?: $this->currentSample;
                    $metaModel = $this->modelFactory->getMetadata($data['name'], $data['value'], $sample);
                    $metaModel->save();
                } catch (\Exception $e) {
                    throw new DatasetWriterException($e->getMessage(), 0, $e);
                }
                return $metaModel;
                break;
            case Descriptor::TYPE_METADATA_INDEX:
                try {
                    $indexModel = $this->modelFactory->getMetadataIndex($data['name']);
                    $indexModel->save();
                } catch (\Exception $e) {
                    throw new DatasetWriterException($e->getMessage(), 0, $e);
                }
                return $indexModel;
                break;
            default:
                throw new DatasetWriterException('Unsupported data type');
        }
    }
}