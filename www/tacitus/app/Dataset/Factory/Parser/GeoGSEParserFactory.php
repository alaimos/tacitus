<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Factory\Parser;

use App\Dataset\Downloader\GeoGSEDownloader;
use App\Dataset\Factory\AbstractParserFactory;
use App\Dataset\Factory\Model\GeoGSEModelFactory;
use App\Dataset\ImportJob\GeoGSEImportJob;
use App\Dataset\Parser\GeoGSEDataParser;
use App\Dataset\Writer\GeoGSEWriter;

/**
 * Class GeoGSEParserFactory
 *
 * @package App\Dataset\Factory\Parser
 */
class GeoGSEParserFactory extends AbstractParserFactory
{

    /**
     * Class name of the downloader object
     *
     * @var string
     */
    protected $downloaderClass = GeoGSEDownloader::class;

    /**
     * Class name of the model factory object
     *
     * @var string
     */
    protected $modelFactoryClass = GeoGSEModelFactory::class;

    /**
     * Class name of the data parser object
     *
     * @var string
     */
    protected $dataParserClass = GeoGSEDataParser::class;

    /**
     * Class name of the dataset writer object
     *
     * @var string
     */
    protected $datasetWriterClass = GeoGSEWriter::class;

    /**
     * Class name of the real importer object
     *
     * @var string
     */
    protected $importJobClass = GeoGSEImportJob::class;

    /**
     * Register this object. Returns the list of data sources this parser is able to handle.
     * The list is in the format [ 'name' => 'User-Friendly Name' ]
     *
     * @return string
     */
    public static function register()
    {
        return ['geogse' => 'NCBI GEO series (GSE)'];
    }
}