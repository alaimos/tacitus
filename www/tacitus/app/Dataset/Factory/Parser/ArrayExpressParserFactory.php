<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Factory\Parser;

use App\Dataset\DescriptorAwareInterface;
use App\Dataset\Downloader\ArrayExpressDownloader;
use App\Dataset\Factory\AbstractParserFactory;
use App\Dataset\Factory\ArrayExpressDataParser;
use App\Dataset\Factory\Model\ArrayExpressModelFactory;
use App\Dataset\JobDataAwareInterface;
use App\Dataset\LogCallbackAwareInterface;
use App\Dataset\ModelFactoryAwareInterface;
use App\Dataset\Registry\SampleRegistry;
use App\Dataset\SampleRegistryAwareInterface;
use App\Dataset\UseDescriptorTrait;
use App\Dataset\UseJobDataTrait;
use App\Dataset\Writer\DefaultDatasetWriter;

class ArrayExpressParserFactory extends AbstractParserFactory
{

    /**
     * Class name of the downloader object
     *
     * @var string
     */
    protected $downloaderClass = ArrayExpressDownloader::class;

    /**
     * Class name of the model factory object
     *
     * @var string
     */
    protected $modelFactoryClass = ArrayExpressModelFactory::class;

    /**
     * Class name of the data parser object
     *
     * @var string
     */
    protected $dataParserClass = ArrayExpressDataParser::class;

    /**
     * Class name of the dataset writer object
     *
     * @var string
     */
    protected $datasetWriterClass = DefaultDatasetWriter::class;

    /**
     * Register this object. Returns the list of data sources this parser is able to handle.
     * The list is in the format [ 'name' => 'User-Friendly Name' ]
     *
     * @return string
     */
    public static function register()
    {
        return ['arrexp' => 'ArrayExpress'];
    }
}