<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\ImportJob;

/**
 * Class ArrayExpressImportJob
 *
 * @package App\Dataset\ImportJob
 */
class ArrayExpressImportJob extends AbstractImportJob
{

    /**
     * Runs an import job.
     *
     * @return void
     * @throws \App\Dataset\Downloader\Exception\DownloaderException
     * @throws \App\Dataset\Writer\Exception\DatasetWriterException
     * @throws \App\Dataset\Parser\Exception\DataParserException
     * @throws \App\Dataset\Downloader\Exception\DownloaderException
     */
    public function run()
    {
        // TODO: Implement run() method.
    }
}