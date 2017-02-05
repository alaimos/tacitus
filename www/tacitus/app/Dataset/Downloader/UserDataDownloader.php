<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Downloader;

use App\Dataset\Descriptor;
use App\Dataset\Downloader\Exception\DownloaderException;
use App\Utils\MultiFile;

/**
 * Class UserData Downloader. This is not a real downloader. It only prepares a descriptor object for this dataset.
 *
 * @package App\Dataset\Downloader
 */
class UserDataDownloader extends AbstractDownloader
{

    /**
     * The number of samples in the metadata file
     *
     * @var int|null
     */
    protected $numberOfSamples = null;

    /**
     * Get properties of the metadata file
     *
     * @param string $file
     *
     * @return array
     */
    private function checkMetadataProperties($file)
    {
        $hasSampleIdHeader = false;
        $this->numberOfSamples = 0;
        $fp = fopen($file, 'r');
        if (!$fp) throw new DownloaderException('Unable to read metadata file');
        $countHeaders = count(explode("\t", fgets($fp)));
        while (($line = fgets($fp)) !== false) {
            $count = count(explode("\t", $line));
            if ($count == $countHeaders) {
                $hasSampleIdHeader = true;
                $this->numberOfSamples++;
            } elseif (($count - 1) == $countHeaders) {
                $this->numberOfSamples++;
            }
        }
        @fclose($fp);
        return [
            'hasSampleIdHeader' => $hasSampleIdHeader,
        ];
    }

    /**
     * Get properties of the data file
     *
     * @param string $file
     *
     * @return array
     */
    private function checkDataProperties($file)
    {
        $fp = fopen($file, 'r');
        if (!$fp) throw new DownloaderException('Unable to read metadata file');
        $countHeaders = count(explode("\t", fgets($fp)));
        @fclose($fp);
        $hasSampleIdHeader = (($countHeaders - 1) == $this->numberOfSamples);
        return [
            'hasSampleIdHeader' => $hasSampleIdHeader,
        ];
    }

    /**
     * Checks, uncompress and store file
     *
     * @param \App\Dataset\Descriptor $descriptor
     * @param string                  $type
     * @param string                  $key
     */
    private function checkFile(Descriptor $descriptor, $type, $key)
    {
        $filename = $this->jobData->getData($key);
        if (MultiFile::isGZipped($filename)) {
            rename($filename, $filename . '.gz');
            $filename = $this->gunzipFile($filename . '.gz', true);
        } elseif (MultiFile::isZipped($filename)) {
            $output = $this->unzipFile($filename, $filename . '.out', true);
            if (!count($output)) {
                throw new DownloaderException('Unable to find content inside zip file');
            }
            $filename = array_shift($output);
        }
        $meta = null;
        if ($type == Descriptor::TYPE_METADATA) {
            $meta = $this->checkMetadataProperties($filename);
        } elseif ($type == Descriptor::TYPE_DATA) {
            $meta = $this->checkDataProperties($filename);
        }
        $descriptor->addFile($filename, $type, $meta);
    }

    /**
     * Run dataset download
     *
     * @return \App\Dataset\Descriptor
     * @throws \App\Dataset\Downloader\Exception\DownloaderException
     */
    public function download()
    {
        $descriptor = new Descriptor($this->jobData);
        $descriptor->addDescriptor([
            'id'   => $this->jobData->getData('original_id'),
            'name' => $this->jobData->getData('title'),
        ]);
        $this->checkFile($descriptor, Descriptor::TYPE_METADATA, 'metadataFile');
        $this->checkFile($descriptor, Descriptor::TYPE_DATA, 'dataFile');
        $metaFiles = $descriptor->getFiles(Descriptor::TYPE_METADATA);
        foreach ($metaFiles as $file) {
            $descriptor->addFile($file, Descriptor::TYPE_METADATA_INDEX,
                $descriptor->getFilesMetadata(Descriptor::TYPE_METADATA, $file));
        }
        return $descriptor;
    }
}