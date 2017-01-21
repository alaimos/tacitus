<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Jobs;

use App\Jobs\Exception\JobException;
use App\Models\Integration;
use App\Models\Job as JobData;
use App\Models\MappedSampleSelection;
use App\Models\Platform;
use App\Models\PlatformMapping;
use App\Models\SampleSelection;
use App\Utils\MultiFile;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IntegrateSelections extends Job implements ShouldQueue
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
        if ($this->jobData->job_type != 'integrate_selections') {
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
     * Create the integration model object
     *
     * @param \App\Models\SampleSelection[]       $selections
     * @param \App\Models\MappedSampleSelection[] $mappedSelections
     * @param Platform                            $platform
     * @param PlatformMapping                     $mapping
     *
     * @return Integration
     */
    protected function createIntegration(array $selections, array $mappedSelections, $platform, $mapping)
    {
        $this->log('Building database object');
        $integration = Integration::create([
            'name'                => $this->jobData->job_data['name'],
            'slug'                => str_slug($this->jobData->job_data['name']),
            'status'              => Integration::PENDING,
            'enable_post_mapping' => $this->jobData->job_data['enable_post_mapping'],
            'user_id'             => $this->jobData->user->id,
            'platform_id'         => ($platform === null) ? null : $platform->id,
            'mapping_id'          => ($mapping === null) ? null : $mapping->id,
            'generated_files'     => [],
        ]);
        foreach ($selections as $selection) {
            $integration->selections()->attach($selection->id);
        }
        foreach ($mappedSelections as $selection) {
            $integration->mappedSelections()->attach($selection->id);
        }
        $integration->save();
        $this->log("...OK\n");
        return $integration;
    }

    /**
     * Create the configuration file to run the integrator script
     *
     * @param Integration $integration
     *
     * @return array
     */
    protected function createConfigFile(Integration $integration)
    {
        $config = [
            'selections' => [],
            'output'     => [
                'data'     => $integration->getFileName('data', 'tsv'),
                'metadata' => $integration->getFileName('metadata', 'tsv'),
            ],
            'method'     => $this->jobData->job_data['method'],
            'digits'     => (int)$this->jobData->job_data['digits'],
            'na.strings' => array_map('trim', explode(',', $this->jobData->job_data['na_strings'])),
        ];
        foreach ($integration->selections as $selection) {
            $config['selections'][] = [
                'data'     => $selection->getDataFilename(),
                'metadata' => $selection->getMetadataFilename(),
            ];
        }
        foreach ($integration->mappedSelections as $selection) {
            $config['selections'][] = [
                'data'     => $selection->getDataFilename(),
                'metadata' => $selection->getMetadataFilename(),
            ];
        }
        $fileName = $integration->getFileName('integrator-config', 'json');
        file_put_contents($fileName, json_encode($config));
        return [$fileName, $config['output']['data'], $config['output']['metadata']];
    }

    /**
     * Runs the integration procedure
     *
     * @param Integration $integration
     *
     * @return string
     */
    protected function runIntegrator(Integration $integration)
    {
        $this->log('Running integration procedure');
        $this->log('...Writing config file');
        list($configFile, $dataFile, $metadataFile) = $this->createConfigFile($integration);
        $this->log('...Running procedure');
        $script = resource_path('scripts/integrator.R');
        $statusFile = $integration->getFileName('status', 'json');;
        $command = 'Rscript ' . $script . ' -c ' . escapeshellarg($configFile) . ' -s ' . escapeshellarg($statusFile);
        $output = null;
        exec($command, $output);
        if ($this->getIntegratorResult($integration)) {
            @chmod($dataFile, 0777);
            @chmod($metadataFile, 0777);
            $integration->setDataFilename($dataFile);
            $integration->setMetadataFilename($metadataFile);
            $integration->save();
            $this->log("...OK\n");
            @unlink($configFile);
            return "\t" . implode("\n\t", $output);
        }
        return '';
    }

    /**
     * Get the results of the integrator execution
     *
     * @param Integration $integration
     *
     * @return boolean
     */
    protected function getIntegratorResult(Integration $integration)
    {
        $fileName = $integration->getFileName('status', 'json');
        if (!file_exists($fileName)) {
            throw new JobException("Unable to find status file");
        }
        $result = json_decode(trim(file_get_contents($fileName)), true);
        @unlink($fileName);
        if (!is_array($result) || !isset($result['ok'])) {
            throw new JobException("Unable to complete integrator execution.");
        }
        if (!$result['ok']) {
            throw new JobException("Unable to complete integrator execution. Error Message: " . $result['message']);
        }
        return $result['ok'];
    }

    /**
     * Clean the R output file removing all non necessary quotation marks and maps identifier
     *
     * @param Integration $integration
     */
    protected function cleanDataFile(Integration $integration)
    {
        $dataFile = $integration->getDataFilename();
        $tmpFile = $integration->getFileName('temp-data', 'tsv');
        $probes = MultiFile::countLines($dataFile);
        $this->log('Preparing final data file (' . ($probes - 1) . ' probes)');
        $fp = MultiFile::fileOpen($dataFile, 'r');
        $fpW = @fopen($tmpFile, 'w');
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
                @fputcsv($fpW, str_getcsv($line, "\t", '"', "\\"), "\t", '"', "\\");
                $this->log('...Headers');
            } else {
                $line = str_getcsv($line, "\t", '"', "\\");
                if (count($line) >= 1) {
                    if ($integration->enable_post_mapping) {
                        $mapped = $integration->platform->mapValues($integration->mapping, [$line[0]]);
                        $line[0] = array_shift($mapped);
                    }
                }
                @fputcsv($fpW, $line, "\t", '"', "\\");
                $this->logProgress(($current + 1), $probes);
            }
            $current++;
        }
        $this->log("...OK\n");
        @fclose($fpW);
        MultiFile::fileClose($fp);
        @unlink($dataFile);
        @rename($tmpFile, $dataFile);
        @chmod($dataFile, 0777);
        if (!file_exists($dataFile)) {
            throw new JobException('Unable to write final data file');
        }
        $integration->setDataFilename($dataFile)->save();
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
            $this->log("Integrating selections.\n");
            $integration = null;
            try {
                /** @var SampleSelection[] $selections */
                $selections = array_filter(array_map(function ($id) {
                    return SampleSelection::whereId($id)->first();
                }, $this->jobData->job_data['selections']));
                /** @var MappedSampleSelection[] $mappedSelections */
                $mappedSelections = array_filter(array_map(function ($id) {
                    return MappedSampleSelection::whereId($id)->first();
                }, $this->jobData->job_data['mapped_selections']));
                if (!count($selections) && !count($mappedSelections)) {
                    throw new JobException("Unable to continue: you must specify at least one valid selection.");
                }
                $platformId = $this->jobData->job_data['platform'];
                $platform = ($platformId) ? Platform::whereId($platformId)->first() : null;
                $mappingId = $this->jobData->job_data['mapping'];
                $mapping = ($mappingId) ? PlatformMapping::whereId($mappingId)->first() : null;
                $integration = $this->createIntegration($selections, $mappedSelections, $platform, $mapping);
                $this->log($this->runIntegrator($integration) . PHP_EOL);
                $this->cleanDataFile($integration);
                $this->log("Data integrated and ready to be downloaded!\n");
                $integration->status = Integration::READY;
                $integration->save();
                $this->sendNotification($user, 'check-circle',
                    'One of your jobs (id: ' . $this->jobData->id . ') has been processed successfully.');
                $this->sendEmail($user, 'TACITuS Notification - A Job has been completed', 'emails.job_completed');
                $this->jobData->status = JobData::COMPLETED;
                $this->jobData->save();
            } catch (\Exception $e) {
                if ($integration !== null && $integration instanceof Integration) {
                    $integration->delete();
                }
                $this->log("...Failed.\n" . $e->getMessage() . "\n");
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