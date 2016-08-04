<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Parser;


use App\Dataset\Descriptor;
use App\Models\Job as JobData;

/**
 * Interface ParserFactoryInterface
 *
 * @package App\Dataset\Parser
 */
interface ParserFactoryInterface
{

    /**
     * Register this object. Returns the list of data sources this parser is able to handle.
     * The list is in the format [ 'name' => 'User-Friendly Name' ]
     *
     * @return string
     */
    public static function register();

    /**
     * Set the job data object
     *
     * @param \App\Models\Job $jobData
     * @return \App\Dataset\Parser\ParserFactoryInterface
     */
    public function setJobData(JobData $jobData);

    /**
     * Set a data descriptor object
     *
     * @param Descriptor $descriptor
     * @return \App\Dataset\Parser\ParserFactoryInterface
     */
    public function setDescriptor(Descriptor $descriptor);

    /**
     * Get dataset downloader object
     *
     * @return \App\Dataset\Downloader\DownloaderInterface
     */
    public function getDatasetDownloader();

    /**
     * Get a data parser object
     *
     * @return \App\Dataset\Parser\Data\DataParserInterface
     */
    public function getDataParser();

    /**
     * Get a model factory object
     *
     * @return \App\Dataset\ModelFactory\ModelFactoryInterface
     */
    public function getDatasetModelFactory();

    /**
     * Get a dataset writer object
     *
     * @param Descriptor $descriptor
     * @return \App\Dataset\Writer\DatasetWriterInterface
     */
    public function getDatasetWriter();

    /**
     * Get a log callback
     *
     * @return callable
     */
    public function getLogCallback();

}