<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Factory\Parser;

use App\Dataset\Downloader\UserDataDownloader;
use App\Dataset\Factory\AbstractParserFactory;
use App\Dataset\Factory\Model\UserDataModelFactory;
use App\Dataset\ImportJob\UserDataImportJob;
use App\Dataset\Parser\UserDataDataParser;
use App\Dataset\Renderer\UserDataRenderer;
use App\Dataset\Writer\DefaultDatasetWriter;

/**
 * Class UserDataParserFactory
 *
 * @package App\Dataset\Factory\Parser
 */
class UserDataParserFactory extends AbstractParserFactory
{

    /**
     * Class name of the downloader object
     *
     * @var string
     */
    protected $downloaderClass = UserDataDownloader::class;

    /**
     * Class name of the model factory object
     *
     * @var string
     */
    protected $modelFactoryClass = UserDataModelFactory::class;

    /**
     * Class name of the data parser object
     *
     * @var string
     */
    protected $dataParserClass = UserDataDataParser::class;

    /**
     * Class name of the dataset writer object
     *
     * @var string
     */
    protected $datasetWriterClass = DefaultDatasetWriter::class;

    /**
     * Class name of the real importer object
     *
     * @var string
     */
    protected $importJobClass = UserDataImportJob::class;

    /**
     * Register this object. Returns the list of data sources this parser is able to handle.
     * The list is in the format [ 'name' => 'User-Friendly Name' ]
     *
     * @return string
     */
    public static function register()
    {
        return ['userdata' => 'User Data'];
    }

    /**
     * Get a form renderer for this type of parser factory.
     * If null is returned this factory does not require any optional user parameters.
     * This is the default implementation of the method.
     *
     * @return \App\Dataset\Renderer\RendererInterface|null
     */
    public function getFormRenderer()
    {
        return new UserDataRenderer();
    }


}