<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Parser;

use App\Dataset\DescriptorAwareInterface;
use App\Dataset\JobDataAwareInterface;
use App\Dataset\LogCallbackAwareInterface;
use App\Dataset\ModelFactoryAwareInterface;
use App\Dataset\UseDescriptorTrait;
use App\Dataset\UseJobDataTrait;

abstract class AbstractParserFactory implements ParserFactoryInterface
{

    use UseJobDataTrait, UseDescriptorTrait;

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
     * A list of instances for singleton-like access
     *
     * @var array
     */
    protected $instances = [];

    protected $supportedAware = [
        DescriptorAwareInterface::class   => ['getDescriptor', 'setDescriptor'],
        JobDataAwareInterface::class      => ['getJobData', 'setJobData'],
        LogCallbackAwareInterface::class  => ['getLogCallback', 'setLogCallback'],
        ModelFactoryAwareInterface::class => ['getDatasetModelFactory', 'setModelFactory'],
    ];


    /**
     * Build an object from its class name
     *
     * @param string $class
     * @return object
     */
    protected function buildObject($class)
    {
        if (isset($this->instances[$class]) && $this->instances[$class] instanceof $class) {
            return $this->instances[$class];
        }
        $this->instances[$class] = new $class();
        foreach ($this->supportedAware as $interface => $methods) {
            if ($this->instances[$class] instanceof $interface) {
                call_user_func([$this->instances[$class], $methods[1]], call_user_func([$this, $methods[0]]));
            }
        }
        return $this->instances[$class];
    }

    /**
     * Get a log callback
     *
     * @return callable
     */
    public function getLogCallback()
    {
        if ($this->logCallback === null) {
            $this->logCallback = function ($message, $autoCommit = false) {
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
        return $this->buildObject($this->downloaderClass);
    }

    /**
     * Get a data parser object
     *
     * @return \App\Dataset\Parser\DataParserInterface
     */
    public function getDataParser()
    {
        return $this->buildObject($this->dataParserClass);
    }

    /**
     * Get a model factory object
     *
     * @return \App\Dataset\ModelFactory\ModelFactoryInterface
     */
    public function getDatasetModelFactory()
    {
        return $this->buildObject($this->modelFactoryClass);
    }

    /**
     * Get a dataset writer object
     *
     * @return \App\Dataset\Writer\DatasetWriterInterface
     */
    public function getDatasetWriter()
    {
        return $this->buildObject($this->datasetWriterClass);
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