<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Factory;

use App\Dataset\Contracts\DescriptorAwareInterface;
use App\Dataset\Contracts\JobDataAwareInterface;
use App\Dataset\Contracts\LogCallbackAwareInterface;
use App\Dataset\Contracts\ModelFactoryAwareInterface;
use App\Dataset\Contracts\SampleRegistryAwareInterface;
use App\Dataset\Registry\SampleRegistry;
use App\Dataset\Traits\InteractsWithDescriptor;
use App\Dataset\Traits\InteractsWithJobData;

abstract class AbstractParserFactory implements ParserFactoryInterface
{

    use InteractsWithJobData, InteractsWithDescriptor;

    //const DEBUG = true;

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
     * Class name of the real importer object
     *
     * @var string
     */
    protected $importJobClass;

    /**
     * Log callback
     *
     * @var callable
     */
    protected $logCallback = null;

    /**
     * Sample Registry
     *
     * @var null|\App\Dataset\Registry\SampleRegistry
     */
    protected $sampleRegistry = null;

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

    /**
     * A list of supported aware interfaces for automated dependency injection
     *
     * @var array
     */
    protected $supportedAware = [
        DescriptorAwareInterface::class     => ['getDescriptor', 'setDescriptor'],
        JobDataAwareInterface::class        => ['getJobData', 'setJobData'],
        LogCallbackAwareInterface::class    => ['getLogCallback', 'setLogCallback'],
        ModelFactoryAwareInterface::class   => ['getDatasetModelFactory', 'setModelFactory'],
        SampleRegistryAwareInterface::class => ['getSampleRegistry', 'setSampleRegistry'],
    ];


    /**
     * Build an object from its class name
     *
     * @param string $class
     *
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
                //if (self::DEBUG) {
                echo $message;
                //}
            };
        }
        return $this->logCallback;
    }

    /**
     * Get a sample registry object
     *
     * @return \App\Dataset\Registry\SampleRegistry
     */
    public function getSampleRegistry()
    {
        if ($this->sampleRegistry === null) {
            $this->sampleRegistry = new SampleRegistry();
        }
        return $this->sampleRegistry;
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
     * @return \App\Dataset\Factory\ModelFactoryInterface
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
     * Get the real handler for the import job
     *
     * @return \App\Dataset\ImportJob\ImportJobInterface
     */
    public function getRealImporter()
    {
        return $this->buildObject($this->importJobClass)->setParserFactory($this);
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