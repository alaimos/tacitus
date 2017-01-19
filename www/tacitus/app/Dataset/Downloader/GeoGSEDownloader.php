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

    const GSE_SOFT_FILENAME = '%s_family.soft.gz';
    const GSE_SOFT_URL = 'https://ftp.ncbi.nlm.nih.gov/geo/series/%s/%s/soft/%s';
    const GSE_MATRIX_FILENAME = '%s_series_matrix.txt.gz';
    const GSE_MATRIX_URL = 'https://ftp.ncbi.nlm.nih.gov/geo/series/%s/%s/matrix/%s';
    const PREFIX_REGEXP = '/\\d{1,3}$/';
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
     * Read title from a GSE series matrix
     *
     * @param string $file
     * @return array
     */
    protected function readGSEInfo($file)
    {
        $fp = gzopen($file, 'r');
        if (!$fp) {
            throw new DownloaderException('Unable to read GSE series matrix.');
        }
        $info = [
            'id'       => $this->getDatasetId(),
            'title'    => null,
            'platform' => null,
        ];
        while (($data = fgetcsv($fp, null, "\t")) !== false) {
            if (count($data) > 1) {
                if (strtolower($data[0]) == '!series_title') {
                    $info['title'] = $data[1];
                } elseif (strtolower($data[0]) == '!series_platform_id') {
                    $info['platform'] = $data[1];
                }
            }
            if ($info['title'] !== null && $info['platform'] !== null) {
                break;
            }
        }
        @fclose($fp);
        if ($info['title'] === null) {
            throw new DownloaderException('Unable to read GSE title');
        }
        if ($info['platform'] === null) {
            throw new DownloaderException('Unable to read GSE platform');
        }
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
        $prefix = preg_replace(self::PREFIX_REGEXP, self::PREFIX_REPLACEMENT, $id);
        $matrixFilename = sprintf(self::GSE_MATRIX_FILENAME, $id);
        $softFilename = sprintf(self::GSE_SOFT_FILENAME, $id);
        $matrixDownloadUrl = sprintf(self::GSE_MATRIX_URL, $prefix, $id, $matrixFilename);
        $softDownloadUrl = sprintf(self::GSE_SOFT_FILENAME, $prefix, $id, $softFilename);
        $descriptor = new Descriptor($this->jobData);
        $this->downloadFile($matrixDownloadUrl, $matrixFilename);
        $this->downloadFile($softDownloadUrl, $softFilename);
        $matrixFilename = $this->downloadDirectory . '/' . $matrixFilename;
        $softFilename = $this->downloadDirectory . '/' . $softFilename;
        if (!file_exists($matrixFilename)) {
            throw new DownloaderException('Unable to download GSE series matrix.');
        }
        if (!file_exists($softFilename)) {
            throw new DownloaderException('Unable to download GSE SOFT file.');
        }
        $descriptor->addDescriptor($this->readGSEInfo($matrixFilename));
        $descriptor->addFile($matrixFilename, Descriptor::TYPE_DATA);
        $descriptor->addFile($softFilename, Descriptor::TYPE_METADATA);
        return $descriptor;
    }
}