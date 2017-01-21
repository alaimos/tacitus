<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Jobs;

use App\Jobs\Exception\JobException;
use App\Models\Job as JobData;
use App\Models\MappedSampleSelection;
use App\Models\Platform;
use App\Models\PlatformMapping;
use App\Models\SampleSelection;
use App\Utils\MultiFile;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MapDatasetSelection extends Job implements ShouldQueue
{

    use InteractsWithQueue, SerializesModels;

    /**
     * The model which holds all job information
     *
     * @var \App\Models\Job
     */
    protected $jobData;

    /**
     * @var integer
     */
    protected $prevPercentage;

    /**
     * ImportDataset constructor.
     *
     * @param \App\Models\Job $jobData
     */
    public function __construct(JobData $jobData)
    {
        $this->jobData = $jobData;
        if ($this->jobData->job_type != 'map_dataset_selection') {
            throw new JobException('This job cannot be run by this class.');
        }
        $this->onQueue('analysis'); // Set the default queue for this job
    }

    /**
     * Print a log message
     *
     * @param string $message
     *
     * @return $this
     */
    protected function log($message)
    {
        $this->jobData->log = $this->jobData->log . $message;
        $this->jobData->save();
        return $this;
    }

    /**
     * Log progress percentage
     *
     * @param integer $current
     * @param integer $total
     *
     * @return void
     */
    protected function logProgress($current, $total)
    {
        $percentage = floor(min(100, ((float)$current / (float)$total) * 100));
        if (($percentage % 10) == 0 && $percentage != 100 && $percentage != $this->prevPercentage) {
            $this->log('...' . $percentage . '%');
        }
        $this->prevPercentage = $percentage;
    }

    /**
     * Create the mapped sample selection object
     *
     * @param \App\Models\SampleSelection $selection
     * @param \App\Models\Platform        $platform
     * @param \App\Models\PlatformMapping $mapping
     *
     * @return MappedSampleSelection
     */
    protected function createMappedSampleSelection(SampleSelection $selection, Platform $platform,
        PlatformMapping $mapping)
    {
        $this->log('Building database object');
        $mappedSelection = MappedSampleSelection::create([
            'selection_id'    => $selection->id,
            'platform_id'     => $platform->id,
            'mapping_id'      => $mapping->id,
            'status'          => MappedSampleSelection::PENDING,
            'user_id'         => $this->jobData->user->id,
            'generated_files' => [],
        ]);
        $this->log("...OK\n");
        return $mappedSelection;
    }

    /**
     * Build metadata file
     *
     * @param \App\Models\MappedSampleSelection $mappedSelection
     * @param \App\Models\SampleSelection       $sampleSelection
     *
     * @return void
     */
    protected function buildMetadataFile(MappedSampleSelection $mappedSelection, SampleSelection $sampleSelection)
    {
        $metadataFile = $sampleSelection->getMetadataFilename();
        $mappedMetadataFile = $mappedSelection->getFileName('metadata', 'tsv');
        $this->log('Writing metadata file');
        if (copy($metadataFile, $mappedMetadataFile)) {
            chmod($mappedMetadataFile, 0777);
            $mappedSelection->setMetadataFilename($mappedMetadataFile)->save();
            $this->log("...OK\n");
        } else {
            throw  new JobException('Unable to write metadata file.');
        }
    }

    /**
     * Build a data file
     *
     * @param MappedSampleSelection $mappedSelection
     * @param SampleSelection       $sampleSelection
     * @param Platform              $platform
     * @param PlatformMapping       $mapping
     */
    protected function buildDataFile(MappedSampleSelection $mappedSelection, SampleSelection $sampleSelection,
        Platform $platform, PlatformMapping $mapping)
    {
        $dataFile = $sampleSelection->getDataFilename();
        $mappedDataFile = $mappedSelection->getFileName('data', 'tsv');
        $probes = MultiFile::countLines($dataFile);
        $this->log('Writing data file (' . ($probes - 1) . ' probes)');
        $fp = MultiFile::fileOpen($dataFile, 'r');
        $fpW = @fopen($mappedDataFile, 'w');
        if (!MultiFile::fileIsOpen($fp)) {
            throw new JobException('Unable to read original data file');
        }
        if (!$fpW) {
            throw new JobException('Unable to create data file');
        }
        $this->prevPercentage = $current = 0;
        while (($line = MultiFile::fileReadLine($fp)) !== false) {
            $line = trim($line);
            if ($current === 0) {
                @fwrite($fpW, $line . PHP_EOL);
                $this->log('...Headers');
            } else {
                $line = str_getcsv($line, "\t", '"', "\\");
                if (count($line) >= 1) {
                    $mapped = $platform->mapValues($mapping, [$line[0]]);
                    $line[0] = array_shift($mapped);
                }
                @fputcsv($fpW, $line, "\t", '"', "\\");
                $this->logProgress(($current + 1), $probes);
            }
            $current++;
        }
        $this->log("...OK\n");
        @fclose($fpW);
        MultiFile::fileClose($fp);
        chmod($mappedDataFile, 0777);
        $mappedSelection->setDataFilename($mappedDataFile)->save();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = $this->jobData->user;
        if ($this->attempts() > 1) {
            $this->delete();
        } else {
            $this->jobData->status = JobData::PROCESSING;
            $this->jobData->save();
            $this->sendNotification($user, 'comment',
                'One of your jobs (id: ' . $this->jobData->id . ') started processing.');
            $this->log("Map Dataset Selection job is starting.\n");
            $this->log('Looking for selection');
            $selection = SampleSelection::whereId($this->jobData->job_data['selection'])->first();
            if ($selection !== null) {
                $this->log("...OK\n");
                $this->log('Looking for platform');
                $platform = Platform::whereId($this->jobData->job_data['platform'])->first();
                if ($platform !== null) {
                    $this->log("...OK\n");
                    $this->log('Looking for mapping');
                    $mapping = PlatformMapping::whereId($this->jobData->job_data['mapping'])->first();
                    if ($mapping !== null) {
                        $this->log("...OK\n");
                        $mappedSelection = null;
                        try {
                            $mappedSelection = $this->createMappedSampleSelection($selection, $platform, $mapping);
                            $this->buildMetadataFile($mappedSelection, $selection);
                            $this->buildDataFile($mappedSelection, $selection, $platform, $mapping);
                            $this->log("Mapped Selection ready to be downloaded!\n");
                            $mappedSelection->status = MappedSampleSelection::READY;
                            $ok = true;
                        } catch (\Exception $e) {
                            $this->log("\n");
                            $errorClass = join('', array_slice(explode('\\', get_class($e)), -1));
                            $this->log('Unable to complete job. Error "' . $errorClass . '" with message "'
                                       . $e->getMessage() . "\".\n");
                            if ($mappedSelection !== null && $mappedSelection instanceof MappedSampleSelection) {
                                $mappedSelection->delete();
                                $mappedSelection = null;
                            }
                            $ok = false;
                        }
                        if ($mappedSelection !== null && $mappedSelection instanceof MappedSampleSelection) {
                            $mappedSelection->save();
                        }
                    } else {
                        $ok = false;
                        $this->log("...Failed.\nUnable to find the specified mapping.\n");
                    }
                } else {
                    $ok = false;
                    $this->log("...Failed.\nUnable to find the specified platform.\n");
                }
            } else {
                $ok = false;
                $this->log("...Failed.\nUnable to find the specified selection.\n");
            }
            if ($ok) {
                $this->sendNotification($user, 'check-circle',
                    'One of your jobs (id: ' . $this->jobData->id . ') has been processed successfully.');
                $this->sendEmail($user, 'TACITuS Notification - A Job has been completed', 'emails.job_completed');
                $this->jobData->status = JobData::COMPLETED;
                $this->jobData->save();
            } else {
                $this->sendNotification($user, 'exclamation-triangle',
                    'One of your jobs (id: ' . $this->jobData->id . ') failed processing. Please check the ' .
                    'error log, correct the errors and submit a new request. Contact us ' .
                    'if you believe a bug is present in our system.');
                $this->sendEmail($user, 'TACITuS Notification - A Job Failed', 'emails.job_failed');
                $this->jobData->status = JobData::FAILED;
                $this->jobData->save();
            }
            $this->delete();
        }
    }

    /**
     * Delete the job
     *
     * @return void
     */
    public function destroy()
    {
        $this->jobData->deleteJobDirectory();
    }

}