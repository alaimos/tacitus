<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\ImportJob;

use App\Dataset\Factory\ParserFactoryInterface;
use App\Dataset\Contracts\JobDataAwareInterface;
use App\Dataset\Contracts\LogCallbackAwareInterface;

/**
 * Interface ImportJobInterface
 *
 * @package App\Dataset\ImportJob
 */
interface ImportJobInterface extends JobDataAwareInterface, LogCallbackAwareInterface
{

    /**
     * Set a ParserFactory instance
     *
     * @param \App\Dataset\Factory\ParserFactoryInterface $parserFactory
     * @return $this
     */
    public function setParserFactory(ParserFactoryInterface $parserFactory);

    /**
     * Get the ParserFactory instance
     *
     * @return \App\Dataset\Factory\ParserFactoryInterface
     */
    public function getParserFactory();


    /**
     * Runs an import job.
     *
     * @return boolean
     * @throws \App\Dataset\Downloader\Exception\DownloaderException
     * @throws \App\Dataset\Writer\Exception\DatasetWriterException
     * @throws \App\Dataset\Parser\Exception\DataParserException
     * @throws \App\Dataset\Downloader\Exception\DownloaderException
     */
    public function run();

}