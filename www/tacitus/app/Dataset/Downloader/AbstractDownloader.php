<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Downloader;

use App\Dataset\Downloader\Exception\DownloaderException;
use App\Dataset\Traits\InteractsWithJobData;
use App\Dataset\Traits\InteractsWithLogCallback;
use App\Utils\Exception\DownloadException;
use App\Utils\Utils;

/**
 * Class AbstractDownloader
 *
 * @package App\Dataset\Downloader
 */
abstract class AbstractDownloader implements DownloaderInterface
{

    use InteractsWithJobData, InteractsWithLogCallback;

    /**
     * The directory where downloads will be stored
     *
     * @var string
     */
    protected $downloadDirectory;

    /**
     * Set the path where downloaded files will be stored
     *
     * @param string $directory
     *
     * @return \App\Dataset\Downloader\DownloaderInterface
     */
    public function setDownloadDirectory($directory)
    {
        $this->downloadDirectory = $directory;
        return $this;
    }

    /**
     * Download a file from a source
     *
     * @param string $source
     * @param string $targetFileName
     *
     * @return bool
     */
    protected function downloadFile($source, $targetFileName)
    {
        try {
            $target = $this->downloadDirectory . '/' . $targetFileName;
            return Utils::downloadFile($source, $target, function ($target, $source, $size) {
                $displaySize = Utils::displaySize($size);
                $this->log('Downloading "' . $target . '" from "' . $source . '" (' . $displaySize . ')', true);
            }, function ($target, $source, $size) {
                $this->log("...OK\n", true);
            }, function ($target, $source, $size, $currentByte, $percentage) {
                $this->log('...' . $percentage . '%', true);
            }, function ($target, $source, $size) {
                $this->log("...Already downloaded!\n", true);
            });
        } catch (DownloadException $ex) {
            throw new DownloaderException($ex->getMessage(), 0, $ex);
        }
    }

    /**
     * Unzip a file and returns the list of output files
     *
     * @param string $filename
     * @param string $outputDirectory
     * @param bool   $fullPath
     *
     * @return array
     */
    protected function unzipFile($filename, $outputDirectory, $fullPath = false)
    {
        $filename = ($fullPath) ? $filename : $this->downloadDirectory . '/' . $filename;
        $outputDirectory = ($fullPath) ? $outputDirectory : $this->downloadDirectory . '/' . $outputDirectory;
        if (!file_exists($outputDirectory)) {
            mkdir($outputDirectory);
        }
        exec('unzip -o -d ' . escapeshellarg($outputDirectory) . ' ' . escapeshellarg($filename));
        return array_map(function ($x) use ($outputDirectory) {
            return $outputDirectory . '/' . $x;
        }, array_diff(scandir($outputDirectory), ['.', '..']));
    }

    /**
     * Unzip a file compressed with gzip and return the new filename
     *
     * @param string $fileName
     * @param bool   $fullPath
     *
     * @return string
     */
    protected function gunzipFile($fileName, $fullPath = false)
    {
        $filePath = ($fullPath) ? $fileName : $this->downloadDirectory . '/' . $fileName;
        //$outputDirectory = dirname($fileName);
        //$base = scandir($outputDirectory);
        exec('gunzip -f ' . escapeshellarg($filePath));
        return str_replace('.gz', '', $fileName);
        //return array_diff(scandir($outputDirectory), $base);
    }

}