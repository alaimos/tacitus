<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Writer;

use App\Dataset\Traits\InteractsWithModelFactory;
use App\Dataset\Traits\InteractsWithSampleRegistry;
use App\Dataset\Writer\Exception\DatasetWriterException;
use App\Models\Sample;

/**
 * Class AbstractDatasetWriter
 *
 * @package App\Dataset\Writer
 */
abstract class AbstractDatasetWriter implements DatasetWriterInterface
{

    use InteractsWithModelFactory, InteractsWithSampleRegistry;

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

    /**
     * Checks if an array appears to be 2-dimensional
     *
     * @param array $data
     * @return bool
     */
    protected function is2DArray(array $data)
    {
        return (is_array(reset($data)));
    }

    /**
     * Get a sample object from a data array, looking up at the sample registry if needed.
     *
     * @param array $data
     * @return \App\Models\Sample|null
     */
    protected function getSample(array $data)
    {
        $sample = null;
        if (isset($data['sample'])) {
            if ($data['sample'] instanceof Sample) {
                $sample = $data['sample'];
            } elseif (is_string($data['sample'])) {
                $sample = $this->sampleRegistry->get($data['sample']);
                if ($sample === null) {
                    $sample = $this->sampleRegistry->getByName($data['sample']);
                }
            } elseif (is_numeric($data['sample'])) {
                $sample = $this->sampleRegistry->getByPosition($data['sample']);
            }
        } elseif (isset($data['sampleId'])) {
            $sample = $this->sampleRegistry->get($data['sampleId']);
        } elseif (isset($data['sampleName'])) {
            $sample = $this->sampleRegistry->getByName($data['sampleName']);
        } elseif (isset($data['samplePosition'])) {
            $sample = $this->sampleRegistry->getByPosition($data['samplePosition']);
        }
        return ($sample === null) ? $this->currentSample : $sample;
    }

    /**
     * Remove sample object from a data array
     *
     * @param array $data
     * @return void
     */
    protected function removeSample(array &$data)
    {
        if (isset($data['sample'])) {
            unset($data['sample']);
        }
        if (isset($data['sampleId'])) {
            unset($data['sampleId']);
        }
        if (isset($data['sampleName'])) {
            unset($data['sampleName']);
        }
        if (isset($data['samplePosition'])) {
            unset($data['samplePosition']);
        }
    }

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
        $type = str_replace(["\t", '\\', '/', ' ', "\r", "\n"], '', $type);
        if (!empty($type)) {
            try {
                $methodName = 'write' . ucfirst($type);
                if (method_exists($this, $methodName)) {
                    return call_user_func([$this, $methodName], $data);
                }
            } catch (\Exception $e) {
                throw new DatasetWriterException($e->getMessage(), 0, $e);
            }
        }
        throw new DatasetWriterException('Unsupported type "' . $type . '".');
    }


}