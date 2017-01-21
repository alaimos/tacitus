<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Downloader;

use App\Dataset\Descriptor;
use App\Dataset\Downloader\Exception\DownloaderException;

/**
 * Class GeoGSEDownloader
 *
 * @package App\Dataset\Downloader
 */
class GeoGSEDownloader extends AbstractDownloader
{

    /**
     * Pattern used to build the URL for the download of the GPL SOFT file
     */
    const GPL_SOFT_URL = 'https://ftp.ncbi.nlm.nih.gov/geo/platforms/%s/%s/soft/%s';

    /**
     * Pattern used to build the GSE SOFT filename
     */
    const GSE_SOFT_FILENAME = '%s_family.soft.gz';

    /**
     * Pattern used to build the URL for the download of the GSE SOFT file
     */
    const GSE_SOFT_URL = 'https://ftp.ncbi.nlm.nih.gov/geo/series/%s/%s/soft/%s';

    /**
     * Pattern used to build the GSE Series Matrix filename with multiple platforms
     */
    const GSE_MATRIX_MULTI_PLATFORM_FILENAME = '%s-%s_series_matrix.txt.gz';

    /**
     * Pattern used to build the GSE Series Matrix filename with a single platform
     */
    const GSE_MATRIX_FILENAME = '%s_series_matrix.txt.gz';

    /**
     * Pattern used to build the URL for the download of the GSE Series Matrix file
     */
    const GSE_MATRIX_URL = 'https://ftp.ncbi.nlm.nih.gov/geo/series/%s/%s/matrix/%s';

    /**
     * Regular expression used to find GSE title in a SOFT file
     */
    const SOFT_TITLE_ROW = '/!series_title\s+=\s+(.*)/i';

    /**
     * Regular expression used to find GSE platforms in a SOFT file
     */
    const SOFT_PLATFORM_ROW = '/!series_platform_id\s+=\s+(.*)/i';

    /**
     * Regular expression used to find the beginning of the series section in the SOFT file
     */
    const SOFT_SERIES_BEGIN = '/^\\^series/i';

    /**
     * Regular expression used to find the end of the series section in the SOFT file
     */
    const SOFT_SERIES_END = '/^\\^(dataset|sample|series|platform|annotation)/i';


    const PREFIX_REGEXP      = '/\\d{1,3}$/';
    const PREFIX_REPLACEMENT = 'nnn';

    /**
     * Get the identifier of the ArrayExpress experiment
     *
     * @return string
     */
    protected function getDatasetId()
    {
        return $this->jobData->job_data['original_id'];
    }

    /**
     * Read preliminary data from a GSE SOFT FILE
     *
     * @param string $file
     *
     * @return array
     */
    protected function readGSEInfo($file)
    {
        $fp = fopen($file, 'r');
        if (!$fp) {
            throw new DownloaderException('Unable to read GSE series matrix.');
        }
        $info = [
            'id'        => $this->getDatasetId(),
            'title'     => null,
            'platforms' => [],
            'platform'  => null,
        ];
        $inSeries = false;
        while (($line = fgets($fp)) !== false) {
            $line = trim($line);
            $matches = null;
            if (!$inSeries && preg_match(self::SOFT_SERIES_BEGIN, $line)) {
                $inSeries = true;
            } elseif ($inSeries && preg_match(self::SOFT_SERIES_END, $line)) {
                break;
            } elseif ($inSeries && preg_match(self::SOFT_TITLE_ROW, $line, $matches)) {
                $info['title'] = $matches[1];
            } elseif ($inSeries && preg_match(self::SOFT_PLATFORM_ROW, $line, $matches)) {
                $info['platforms'][] = $matches[1];
            }
        }
        @fclose($fp);
        if ($info['title'] === null) {
            throw new DownloaderException('Unable to read GSE title');
        }
        if (!count($info['platforms'])) {
            throw new DownloaderException('Unable to read GSE platform');
        }
        $info['multi_platform'] = count($info['platforms']) > 1;
        $info['platform'] = $info['platforms'][0];
        return $info;
    }

    /**
     * Run dataset download
     *
     * @return \App\Dataset\Descriptor
     * @throws \App\Dataset\Downloader\Exception\DownloaderException
     */
    public function download()
    {
        $id = $this->getDatasetId();
        $descriptor = new Descriptor($this->jobData);
        $prefix = preg_replace(self::PREFIX_REGEXP, self::PREFIX_REPLACEMENT, $id);
        $softFilename = sprintf(self::GSE_SOFT_FILENAME, $id);
        $softDownloadUrl = sprintf(self::GSE_SOFT_URL, $prefix, $id, $softFilename);
        $this->downloadFile($softDownloadUrl, $softFilename);
        $softFilename = $this->downloadDirectory . '/' . $this->gunzipFile($softFilename);
        if (!file_exists($softFilename)) {
            throw new DownloaderException('Unable to download GSE SOFT file.');
        }
        $this->log('Reading GSE Metadata from SOFT file', true);
        $info = $this->readGSEInfo($softFilename);
        $this->log("...OK\n", true);
        $descriptor->addFile($softFilename, Descriptor::TYPE_METADATA_INDEX);
        $descriptor->addFile($softFilename, Descriptor::TYPE_METADATA);
        if ($info['multi_platform']) {
            throw new DownloaderException('Multi-Platform Series are not supported. Please download each SubSeries.');
        } else {
            $matrixFilename = sprintf(self::GSE_MATRIX_FILENAME, $id);
            $matrixDownloadUrl = sprintf(self::GSE_MATRIX_URL, $prefix, $id, $matrixFilename);
            $this->downloadFile($matrixDownloadUrl, $matrixFilename);
            $matrixFilename = $this->downloadDirectory . '/' . $this->gunzipFile($matrixFilename);
            if (!file_exists($matrixFilename)) {
                throw new DownloaderException('Unable to download GSE series matrix.');
            }
            $descriptor->addFile($matrixFilename, Descriptor::TYPE_DATA);
            $platform = $info['platform'];
            $platformPrefix = preg_replace(self::PREFIX_REGEXP, self::PREFIX_REPLACEMENT, $platform);
            $platformFilename = sprintf(self::GSE_SOFT_FILENAME, $platform);
            $platformDownloadUrl = sprintf(self::GPL_SOFT_URL, $platformPrefix, $platform, $platformFilename);
            $this->downloadFile($platformDownloadUrl, $platformFilename);
            $platformFilename = $this->downloadDirectory . '/' . $platformFilename;
            if (!file_exists($platformFilename)) {
                throw new DownloaderException('Unable to download GPL SOFT file.');
            }
            $info['platform_file'] = $platformFilename;
        }
        $descriptor->addDescriptor($info);
        return $descriptor;
    }
}