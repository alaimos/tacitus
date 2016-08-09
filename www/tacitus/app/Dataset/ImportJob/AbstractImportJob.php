<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\ImportJob;

use App\Dataset\Factory\ParserFactoryInterface;
use App\Dataset\Traits\InteractsWithJobData;
use App\Dataset\Traits\InteractsWithLogCallback;

/**
 * Class AbstractImportJob
 *
 * @package App\Dataset\ImportJob
 */
abstract class AbstractImportJob implements ImportJobInterface
{

    use InteractsWithJobData, InteractsWithLogCallback;

    /**
     * A parser factory object
     *
     * @var \App\Dataset\Factory\ParserFactoryInterface
     */
    protected $parserFactory;

    /**
     * Set a ParserFactory instance
     *
     * @param \App\Dataset\Factory\ParserFactoryInterface $parserFactory
     * @return $this
     */
    public function setParserFactory(ParserFactoryInterface $parserFactory)
    {
        $this->parserFactory = $parserFactory;
        return $this;
    }

    /**
     * Get the ParserFactory instance
     *
     * @return \App\Dataset\Factory\ParserFactoryInterface
     */
    public function getParserFactory()
    {
        return $this->parserFactory;
    }

}