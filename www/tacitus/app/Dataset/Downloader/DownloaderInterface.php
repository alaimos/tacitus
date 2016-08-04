<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Downloader;

use App\Models\Job as JobData;

/**
 * Interface DownloaderInterface
 *
 * @package App\Dataset\Downloader
 */
interface DownloaderInterface
{

    /**
     * Set the logger callback
     *
     * @param callable $callback
     * @return \App\Dataset\Downloader\DownloaderInterface
     */
    public function setLogCallback(callable $callback);

    /**
     * Set the path where downloaded files will be stored
     *
     * @param string $directory
     * @return \App\Dataset\Downloader\DownloaderInterface
     */
    public function setDownloadDirectory($directory);

    /**
     * Set the job data object
     *
     * @param \App\Models\Job $jobData
     * @return \App\Dataset\Downloader\DownloaderInterface
     */
    public function setJobData(JobData $jobData);

    /**
     * Run dataset download
     *
     * @return \App\Dataset\Descriptor
     */
    public function download();

}