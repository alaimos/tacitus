<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Parser;


use App\Dataset\Descriptor;
use App\Models\Job as JobData;

abstract class AbstractParserFactory implements ParserFactoryInterface
{

    /**
     * Class name of the downloader object
     *
     * @var string
     */
    protected $downloaderClass;

    /**
     * Class name of the model factory object
     *
     * @var string
     */
    protected $modelFactoryClass;

    /**
     * Class name of the data parser object
     *
     * @var string
     */
    protected $dataParserClass;

    /**
     * Class name of the dataset writer object
     *
     * @var string
     */
    protected $datasetWriterClass;

    /**
     * Job data object
     *
     * @var \App\Models\Job
     */
    protected $jobData;

    /**
     * A data descriptor object
     *
     * @var \App\Dataset\Descriptor
     */
    protected $descriptor;

    /**
     * Log callback
     *
     * @var callable
     */
    protected $logCallback = null;

    /**
     * Are there uncommited logs?
     *
     * @var bool
     */
    protected $uncommitedLog = false;

    /**
     * Set the job data object
     *
     * @param \App\Models\Job $jobData
     * @return \App\Dataset\Parser\ParserFactoryInterface
     */
    public function setJobData(JobData $jobData)
    {
        $this->jobData = $jobData;
        return $this;
    }

    /**
     * Set a data descriptor object
     *
     * @param Descriptor $descriptor
     * @return \App\Dataset\Parser\ParserFactoryInterface
     */
    public function setDescriptor(Descriptor $descriptor)
    {
        $this->descriptor = $descriptor;
        return $this;
    }

    /**
     * Get a log callback
     *
     * @return callable
     */
    public function getLogCallback()
    {
        if ($this->logCallback === null) {
            $this->logCallback = function ($message, $autoCommit = false) use ($this) {
                $this->jobData->log = $this->jobData->log . $message;
                if (!$autoCommit) {
                    $this->uncommitedLog = true;
                } else {
                    $this->jobData->save();
                }
            };
        }
        return $this->logCallback;
    }

    /**
     * Get dataset downloader object
     *
     * @return \App\Dataset\Downloader\DownloaderInterface
     */
    public function getDatasetDownloader()
    {
        $class = $this->downloaderClass;
        /** @var \App\Dataset\Downloader\DownloaderInterface $object */
        $object = new $class();
        return $object->setJobData($this->jobData)->setLogCallback($this->logCallback);
    }

    /**
     * Get a data parser object
     *
     * @return \App\Dataset\Parser\Data\DataParserInterface
     */
    public function getDataParser()
    {
        $class = $this->dataParserClass;
        /** @var \App\Dataset\Parser\Data\DataParserInterface $object */
        $object = new $class();
        return $object->setJobData($this->jobData)->setLogCallback($this->logCallback)
            ->setDescriptor($this->descriptor);
    }

    /**
     * Get a model factory object
     *
     * @return \App\Dataset\ModelFactory\ModelFactoryInterface
     */
    public function getDatasetModelFactory()
    {
        $class = $this->modelFactoryClass;
        /** @var \App\Dataset\ModelFactory\ModelFactoryInterface $object */
        $object = new $class();
        return $object->setJobData($this->jobData)->setDescriptor($this->descriptor);
    }

    /**
     * Get a dataset writer object
     *
     * @return \App\Dataset\Writer\DatasetWriterInterface
     */
    public function getDatasetWriter()
    {
        $class = $this->datasetWriterClass;
        /** @var \App\Dataset\Writer\DatasetWriterInterface $object */
        $object = new $class();
        return $object->setJobData($this->jobData)->setLogCallback($this->logCallback)
            ->setDescriptor($this->descriptor);
    }

    /**
     * Commit uncommited logs
     *
     * @return $this
     */
    public function commitLog()
    {
        if ($this->uncommitedLog) {
            $this->jobData->save();
        }
        return $this;
    }

    /**
     * Runs cleanup operations before destruction
     */
    function __destruct()
    {
        $this->commitLog();
    }


}