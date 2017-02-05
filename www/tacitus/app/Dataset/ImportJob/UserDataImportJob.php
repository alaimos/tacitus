<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\ImportJob;

use App\Dataset\Descriptor;
use App\Models\Dataset;

/**
 * Class UserDataImportJob
 *
 * @package App\Dataset\ImportJob
 */
class UserDataImportJob extends AbstractImportJob
{

    /**
     * Runs an import job.
     *
     * @return boolean
     * @throws \App\Dataset\Downloader\Exception\DownloaderException
     * @throws \App\Dataset\Writer\Exception\DatasetWriterException
     * @throws \App\Dataset\Parser\Exception\DataParserException
     * @throws \App\Dataset\Downloader\Exception\DownloaderException
     */
    public function run()
    {
        $dataset = null;
        $ok = false;
        try {
            $this->log("Starting User Data import job.\n", true);
            $jobDirectory = $this->jobData->getJobDirectory();
            $downloader = $this->parserFactory->getDatasetDownloader()->setDownloadDirectory($jobDirectory);
            $descriptor = $downloader->download();
            $this->parserFactory->setDescriptor($descriptor);
            $dataParser = $this->parserFactory->getDataParser();
            $dataWriter = $this->parserFactory->getDatasetWriter();
            $this->log('Creating dataset', true);
            $dataset = $dataWriter->writeDataset();
            $this->log("...OK\n", true);
            $this->log('Parsing metadata index', true);
            $dataParser->start(Descriptor::TYPE_METADATA_INDEX);
            while (($row = $dataParser->parse()) !== null) {
                if (!empty($row)) {
                    $dataWriter->write(Descriptor::TYPE_METADATA_INDEX, $row);
                }
            }
            $this->log("...OK\n", true);
            $this->log('Parsing metadata', true);
            $dataParser->start(Descriptor::TYPE_METADATA);
            $this->initProgress();
            while (($row = $dataParser->parse()) !== null) {
                if (!empty($row)) {
                    $dataWriter->write(Descriptor::TYPE_SAMPLE, $row['sample']);
                    $dataWriter->write(Descriptor::TYPE_METADATA, $row['metadata']);
                }
                $this->logProgress($dataParser->current(), $dataParser->count());
            }
            $this->log("...OK\n", true);
            $this->log('Parsing data', true);
            $dataParser->start(Descriptor::TYPE_DATA);
            $this->initProgress();
            while (($row = $dataParser->parse()) !== null) {
                if (!empty($row)) {
                    $dataWriter->write(Descriptor::TYPE_DATA, $row);
                }
                $this->logProgress($dataParser->current(), $dataParser->count());
            }
            $this->log("...OK\n", true);
            $dataParser->end();
            $this->log("Dataset parsed and ready!\n", true);
            $dataset->status = Dataset::READY;
            $ok = true;
        } catch (\Exception $e) {
            $this->log("\n");
            $errorClass = join('', array_slice(explode('\\', get_class($e)), -1));
            $this->log('Unable to complete job. Error "' . $errorClass . '" with message "' . $e->getMessage()
                       . "\".\n",
                true);
            echo $e->__toString();
            if ($dataset !== null && $dataset instanceof Dataset) {
                $dataset->status = Dataset::FAILED;
            }
        }
        if ($dataset !== null && $dataset instanceof Dataset) {
            $dataset->save();
        }
        return $ok;
    }
}