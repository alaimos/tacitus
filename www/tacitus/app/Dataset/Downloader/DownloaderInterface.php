<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Downloader;

use App\Dataset\JobDataAwareInterface;
use App\Dataset\LogCallbackAwareInterface;

/**
 * Interface DownloaderInterface
 *
 * @package App\Dataset\Downloader
 */
interface DownloaderInterface extends JobDataAwareInterface, LogCallbackAwareInterface
{

    /**
     * Set the path where downloaded files will be stored
     *
     * @param string $directory
     *
     * @return \App\Dataset\Downloader\DownloaderInterface
     */
    public function setDownloadDirectory($directory);

    /**
     * Run dataset download
     *
     * @return \App\Dataset\Descriptor
     * @throws \App\Dataset\Downloader\Exception\DownloaderException
     */
    public function download();

}