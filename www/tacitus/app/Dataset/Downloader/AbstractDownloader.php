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
     * @return bool
     */
    protected function downloadFile($source, $targetFileName)
    {
        $targetFileName = $this->downloadDirectory . '/' . $targetFileName;
        $size = Utils::getDownloadSize($source);
        $displaySize = Utils::displaySize($size);
        $this->log('Downloading "' . $targetFileName . '" from "' . $source . '" (' . $displaySize . ')', true);
        if (file_exists($targetFileName)) { //File caching
            $this->log("...Already downloaded!\n", true);
            return true;
        }
        $rh = fopen($source, 'rb');
        $wh = fopen($targetFileName, 'w+b');
        if (!$rh) {
            throw new DownloaderException("Unable to open source file");
        }
        if (!$wh) {
            throw new DownloaderException("Unable to open destination file");
        }
        $size = (float)$size;
        $currentByte = 0;
        $prevPercentage = 0;
        while (!feof($rh)) {
            if (($tmp = fread($rh, 8192)) !== false) {
                if (fwrite($wh, $tmp, 8192) === false) {
                    throw new DownloaderException("Unable to write to destination");
                }
                $currentByte += strlen($tmp);
                $percentage = floor(min(100, ((float)$currentByte / $size) * 100));
                if (($percentage % 10) == 0 && $percentage != 100 && $percentage != $prevPercentage) {
                    $this->log('...' . $percentage . '%', true);
                }
                $prevPercentage = $percentage;
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
        exec('unzip -o -d ' . escapeshellarg($outputDirectory) . ' ' . escapeshellarg($fileName));
        return array_map(function ($x) use ($outputDirectory) {
            return $outputDirectory . '/' . $x;
        }, array_diff(scandir($outputDirectory), ['.', '..']));
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
        exec('gunzip -f ' . escapeshellarg($fileName));
        return array_diff(scandir($outputDirectory), $base);
    }

}