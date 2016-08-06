<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Downloader;

use App\Dataset\Downloader\Exception\DownloaderException;
use App\Dataset\UseJobDataTrait;
use App\Dataset\UseLogCallbackTrait;

/**
 * Class AbstractDownloader
 *
 * @package App\Dataset\Downloader
 */
abstract class AbstractDownloader implements DownloaderInterface
{

    use UseJobDataTrait, UseLogCallbackTrait;

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
     * @return bool
     */
    protected function downloadFile($source, $targetFileName)
    {
        $this->log('Downloading "' . $targetFileName . '" from "' . $source . '"', true);
        $rh = fopen($source, 'rb');
        $wh = fopen($this->downloadDirectory . '/' . $targetFileName, 'w+b');
        if (!$rh) {
            throw new DownloaderException("Unable to open source file");
        }
        if (!$wh) {
            throw new DownloaderException("Unable to open destination file");
        }
        while (!feof($rh)) {
            if ($tmp = fread($rh, 4096) !== false) {
                if (fwrite($wh, $tmp) === false) {
                    throw new DownloaderException("Unable to write to destination");
                }
            } else {
                throw new DownloaderException("Unable to read from source");
            }
        }
        fclose($rh);
        fclose($wh);
        $this->log("...OK\n", true);
        return true;
    }

    /**
     * Unzip a file and returns the list of output files
     *
     * @param string $fileName
     * @param string $outputDirectory
     * @return array
     */
    protected function unzipFile($fileName, $outputDirectory)
    {
        $fileName = $this->downloadDirectory . '/' . $fileName;
        $outputDirectory = $this->downloadDirectory . '/' . $outputDirectory;
        if (!file_exists($outputDirectory)) {
            mkdir($outputDirectory);
        }
        exec('unzip -d "' . $outputDirectory . '" "' . $fileName . '"');
        return array_diff(scandir($outputDirectory), ['.', '..']);
    }

    /**
     * Unzip a file compressed with gzip and return the new filename
     *
     * @param string $fileName
     * @return string
     */
    protected function gunzipFile($fileName)
    {
        $fileName = $this->downloadDirectory . '/' . $fileName;
        $outputDirectory = dirname($fileName);
        $base = scandir($outputDirectory);
        exec('gunzip "' . $fileName . '"');
        return array_diff(scandir($outputDirectory), $base);
    }

}