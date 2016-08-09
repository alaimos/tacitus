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
 * Class ArrayExpressDownloader
 *
 * @package App\Dataset\Downloader
 */
class ArrayExpressDownloader extends AbstractDownloader
{

    const DATASET_INFO_URL = 'http://www.ebi.ac.uk/arrayexpress/json/v2/experiments/%s';
    const DATASET_FILES_URL = 'http://www.ebi.ac.uk/arrayexpress/json/v2/files/%s';
    const METADATA_KIND = 'sdrf';
    const DATA_KIND = 'processed';
    const SUPPORTED_EXTENSIONS = ['txt', 'zip'];

    /**
     * @var array
     */
    protected $kindsToTypeMap = [
        self::METADATA_KIND => Descriptor::TYPE_METADATA,
        self::DATA_KIND     => Descriptor::TYPE_DATA,
    ];

    /**
     * Get the identifier of the ArrayExpress experiment
     *
     * @return string
     */
    protected function getDatasetId()
    {
        return $this->jobData->job_data['originalId'];
    }

    /**
     * Get descriptive data about this dataset
     *
     * @return array
     */
    protected function getDatasetDescription()
    {
        $url = sprintf(self::DATASET_INFO_URL, $this->getDatasetId());
        $this->log('Loading dataset description', true);
        $data = json_decode(file_get_contents($url), true);
        if (is_array($data) && isset($data['experiments']) && is_array($data['experiments'])
            && isset($data['experiments']['experiment'])
            && is_array($data['experiments']['experiment'])
        ) {
            $experiment = $data['experiments']['experiment'];
            if (isset($experiment['name'])) {
                $this->log("...OK\n", true);
                return [
                    'id'   => $this->getDatasetId(),
                    'name' => $experiment['name'],
                ];
            }
        }
        throw new DownloaderException('Unable to load dataset description.');
    }

    /**
     * Get the list of files
     *
     * @return array
     */
    protected function getFilesData()
    {
        $url = sprintf(self::DATASET_FILES_URL, $this->getDatasetId());
        $this->log('Loading list of files to download', true);
        $data = json_decode(file_get_contents($url), true);
        if (is_array($data) && isset($data['files']) && is_array($data['files'])
            && isset($data['files']['experiment'])
            && is_array($data['files']['experiment'])
            && isset($data['files']['experiment']['file'])
            && is_array($data['files']['experiment']['file'])
        ) {
            $experiment = $data['files']['experiment'];
            $files = [
                self::METADATA_KIND => [],
                self::DATA_KIND     => [],
            ];
            foreach ($experiment['file'] as $file) {
                $extension = strtolower($file['extension']);
                if (!in_array($extension, self::SUPPORTED_EXTENSIONS)) {
                    continue;
                }
                $kind = (array)$file['kind'];
                $tmp = [
                    'name'      => $file['name'],
                    'url'       => $file['url'],
                    'extension' => $extension,
                ];
                if (in_array(self::METADATA_KIND, $kind)) {
                    $files[self::METADATA_KIND][] = $tmp;
                } elseif (in_array(self::DATA_KIND, $kind)) {
                    $files[self::DATA_KIND][] = $tmp;
                }
            }
            if (!count($files)) {
                throw new DownloaderException('No supported files found.');
            }
            $this->log("...OK\n", true);
            return $files;
        }
        throw new DownloaderException('Unable to load list of files.');
    }

    /**
     * Run dataset download
     *
     * @return \App\Dataset\Descriptor
     * @throws \App\Dataset\Downloader\Exception\DownloaderException
     */
    public function download()
    {
        $description = $this->getDatasetDescription();
        $files = $this->getFilesData();
        $descriptor = new Descriptor($this->jobData);
        $descriptor->addDescriptor($description);
        foreach ($files as $kind => $allFiles) {
            foreach ($allFiles as $file) {
                $this->downloadFile($file['url'], $file['name']);
                if ($file['extension'] == 'zip') {
                    $this->log('Extracting "' . $file['name'] . '"', true);
                    $extracted = $this->unzipFile($file['name'], basename($file['name']) . '_out');
                    if (!count($extracted)) {
                        throw new DownloaderException('Unable to extract file content.');
                    }
                    $this->log("...OK\n", true);
                    foreach ($extracted as $extract) {
                        if (file_exists($extract)) {
                            $descriptor->addFile($extract, $this->kindsToTypeMap[$kind]);
                        }
                    }
                } else {
                    $path = $this->downloadDirectory . '/' . $file['name'];
                    $descriptor->addFile($path, $this->kindsToTypeMap[$kind]);
                }
            }
        }
        $descriptor->addFile($descriptor->getFiles(Descriptor::TYPE_METADATA), Descriptor::TYPE_METADATA_INDEX);
        return $descriptor;
    }
}