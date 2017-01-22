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
    const TYPE_OTHER          = 'other';

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
    protected $files = [];

    /**
     * A list of metadata for each file in the "files" array
     *
     * @var array
     */
    protected $filesMetadata = [];

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
     * @param array        $metadata
     *
     * @return \App\Dataset\Descriptor $this
     */
    public function addFile($localPath, $type, array $metadata = null)
    {
        if (is_array($localPath)) {
            foreach ($localPath as $path) {
                $this->addFile($path, $type, $metadata);
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
            if ($metadata !== null && !empty($metadata)) {
                if (!isset($this->filesMetadata[$type])) {
                    $this->filesMetadata[$type] = [];
                }
                if (isset($this->filesMetadata[$type][$localPath])) {
                    $this->filesMetadata[$type][$localPath] += $metadata;
                } else {
                    $this->filesMetadata[$type][$localPath] = $metadata;
                }
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
                $this->filesMetadata[$type] = [];
            }
        } else {
            $this->files[$type] = [];
            $this->filesMetadata[$type] = [];
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
     * Returns file metadata
     *
     * @param string|null $type
     * @param string|null $file
     *
     * @return array|null
     */
    public function getFilesMetadata($type = null, $file = null)
    {
        if ($type === null) {
            return $this->filesMetadata;
        } else {
            if (!isset($this->filesMetadata[$type])) return null;
            if ($file === null) {
                return $this->filesMetadata[$type];
            } else {
                if (!isset($this->filesMetadata[$type][$file])) return null;
                return $this->filesMetadata[$type][$file];
            }
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