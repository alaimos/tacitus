<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset;

use App\Models\Job as JobData;

/**
 * Dataset Descriptor
 *
 * @package App\Dataset
 */
class Descriptor
{

    const TYPE_SAMPLE         = 'sample';
    const TYPE_DATA           = 'data';
    const TYPE_METADATA       = 'metadata';
    const TYPE_METADATA_INDEX = 'metadataIndex';

    /**
     * The job data model object
     *
     * @var \App\Models\Job
     */
    protected $jobData;

    /**
     * A list of local files
     *
     * @var array
     */
    protected $files = [
        self::TYPE_DATA     => [],
        self::TYPE_METADATA => [],
    ];

    /**
     * A list of dataset descriptor variables
     *
     * @var array
     */
    protected $descriptors = [];

    /**
     * Descriptor constructor.
     *
     * @param JobData $jobData
     */
    public function __construct(JobData $jobData)
    {
        $this->jobData = $jobData;
    }

    /**
     * Return job data object
     *
     * @return \App\Models\Job
     */
    public function getJobData()
    {
        return $this->jobData;
    }

    /**
     * Add one or more file of a specific type.
     *
     * @param string|array $localPath
     * @param string       $type
     *
     * @return \App\Dataset\Descriptor  $this
     */
    public function addFile($localPath, $type)
    {
        if (is_array($localPath)) {
            foreach ($localPath as $path) {
                $this->addFile($path, $type);
            }
        } else {
            if (!isset($this->files[$type])) {
                $this->files[$type] = [];
            }
            $localPath = realpath($localPath);
            if ($localPath) {
                $this->files[$type][] = $localPath;
                $this->files[$type] = array_unique($this->files[$type]);
            }
        }
        return $this;
    }

    /**
     * Clear all files
     *
     * @param string|null $type
     *
     * @return \App\Dataset\Descriptor  $this
     */
    public function clearFiles($type = null)
    {
        if ($type === null) {
            foreach ($this->files as $type => $ignore) {
                $this->files[$type] = [];
            }
        } else {
            $this->files[$type] = [];
        }
        return $this;
    }

    /**
     * Return all files, or files of a specific type
     *
     * @param string|null $type
     *
     * @return array
     */
    public function getFiles($type = null)
    {
        if ($type === null) {
            return $this->files;
        } else {
            if (!isset($this->files[$type])) {
                return null;
            }
            return $this->files[$type];
        }
    }

    /**
     * Add descriptor variable
     *
     * @param mixed       $value
     * @param string|null $name
     *
     * @return \App\Dataset\Descriptor  $this
     */
    public function addDescriptor($value, $name = null)
    {
        if ($name === null && is_array($value)) {
            foreach ($value as $key => $val) {
                $this->descriptors[$key] = $val;
            }
        } elseif ($name !== null) {
            $this->descriptors[$name] = $value;
        }
        return $this;
    }

    /**
     * Get descriptor variables
     *
     * @return array
     */
    public function getDescriptors()
    {
        return $this->descriptors;
    }

}