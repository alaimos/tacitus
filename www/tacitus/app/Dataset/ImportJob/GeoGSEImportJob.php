<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\ImportJob;

use App\Dataset\Descriptor;
use App\Models\Dataset;
use App\Models\Platform;
use App\Platform\Import\Factory\PlatformImportFactory;

/**
 * Class GeoGSEImportJob
 *
 * @package App\Dataset\ImportJob
 */
class GeoGSEImportJob extends AbstractImportJob
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
            $this->log("Starting NCBI GEO (GSE) import job.\n", true);
            $jobDirectory = $this->jobData->getJobDirectory();
            $downloader = $this->parserFactory->getDatasetDownloader()->setDownloadDirectory($jobDirectory);
            $descriptor = $downloader->download();
            $vars = $descriptor->getDescriptors();
            $factory = new PlatformImportFactory();
            $platform = $factory->getImporter('SoftFile', [
                'softFile'         => $vars['platform_file'],
                'private'          => $this->jobData->job_data['private'],
                'logCallback'      => $this->getLogCallback(),
                'user'             => $this->jobData->user,
                'importingDataset' => true,
            ])->import()->getPlatform();
            if ($platform !== null) {
                $platform->status = Platform::READY;
                $platform->save();
                $descriptor->addDescriptor($platform->getKey(), 'platform_id');
            } else {
                $descriptor->addDescriptor(null, 'platform_id');
            }
            $this->parserFactory->setDescriptor($descriptor);
            $dataParser = $this->parserFactory->getDataParser();
            $dataWriter = $this->parserFactory->getDatasetWriter();
            $this->log('Creating dataset', true);
            $dataset = $dataWriter->writeDataset();
            $this->log("...OK\n", true);
            $this->log('Parsing metadata index', true);
            $dataParser->start(Descriptor::TYPE_METADATA_INDEX);
            $this->initProgress();
            while (($row = $dataParser->parse()) !== null) {
                if ($row) {
                    $dataWriter->write(Descriptor::TYPE_METADATA_INDEX, $row);
                }
                $this->logProgress($dataParser->current(), $dataParser->count());
            }
            $this->log("...OK\n", true);
            $this->log('Parsing metadata', true);
            $dataParser->start(Descriptor::TYPE_METADATA);
            $this->initProgress();
            while (($row = $dataParser->parse()) !== null) {
                if ($row) {
                    if (is_array($row) && count($row) > 0
                        && !isset($row['sample'])
                    ) { //No terminators, multi-sample case
                        foreach ($row as $meta) {
                            if (isset($meta['sample']) && isset($meta['metadata'])) {
                                $dataWriter->write(Descriptor::TYPE_SAMPLE, $meta['sample']);
                                $dataWriter->write(Descriptor::TYPE_METADATA, $meta['metadata']);
                            }
                        }
                    } else { //Terminators found in soft file
                        $dataWriter->write(Descriptor::TYPE_SAMPLE, $row['sample']);
                        $dataWriter->write(Descriptor::TYPE_METADATA, $row['metadata']);
                    }
                }
                $this->logProgress($dataParser->current(), $dataParser->count());
            }
            $this->log("...OK\n", true);
            $this->log('Parsing data', true);
            $dataParser->start(Descriptor::TYPE_DATA);
            $this->initProgress();
            while (($row = $dataParser->parse()) !== null) {
                if ($row) {
                    $dataWriter->write(Descriptor::TYPE_DATA, $row);
                }
                $this->logProgress($dataParser->current(), $dataParser->count());
            }
            $this->log("...OK\n", true);
            $this->log("Dataset parsed and ready!\n", true);
            $dataset->status = Dataset::READY;
            $ok = true;
        } catch (\Exception $e) {
            $this->log("\n");
            $errorClass = join('', array_slice(explode('\\', get_class($e)), -1));
            $this->log('Unable to complete job. Error "' . $errorClass . '" with message "' . $e->getMessage()
                       . "\".\n",
                true);
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