<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Jobs;

use App\Jobs\Exception\JobException;
use App\Models\Dataset;
use App\Models\Job as JobData;
use App\Models\Probe;
use App\Models\SampleSelection;
use DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class DatasetSelection extends Job implements ShouldQueue
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
        if ($this->jobData->job_type != 'dataset_selection') {
            throw new JobException('This job cannot be run by this class.');
        }
        $this->onQueue('selections'); // Set the default queue for this job
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
     * Create a SampleSelection object
     *
     * @param \App\Models\Dataset $dataset
     *
     * @return SampleSelection
     */
    protected function createSampleSelection(Dataset $dataset)
    {
        $this->log('Building database object');
        $sampleSelection = new SampleSelection([
            'name'             => $this->jobData->job_data['selectionName'],
            'slug'             => Str::slug($this->jobData->job_data['selectionName'], '_'),
            'selected_samples' => $this->jobData->job_data['samples'],
            'generated_files'  => [],
            'status'           => SampleSelection::PENDING,
            'platform_id'      => $dataset->platform_id,
        ]);
        $sampleSelection->dataset()->associate($dataset);
        $sampleSelection->user()->associate($this->jobData->user);
        $sampleSelection->save();
        $this->log("...OK\n");
        return $sampleSelection;
    }

    /**
     * Build metadata file and returns a map from Sample Id to SampleName and Position
     *
     * @param \App\Models\SampleSelection $sampleSelection
     * @param \App\Models\Dataset         $dataset
     *
     * @return array
     */
    protected function buildMetadataFile(SampleSelection $sampleSelection, Dataset $dataset)
    {
        $fileName = $sampleSelection->getFileName('metadata', 'tsv');
        $this->log('Writing metadata file');
        $fp = fopen($fileName, 'w');
        if (!$fp) {
            throw new JobException('Unable to create metadata file');
        }
        $idx = ['name'];
        $tmp = ['Sample Identifier'];
        foreach ($dataset->metadataIndex as $meta) {
            $idx[] = snake_case($meta->name);
            $tmp[] = $meta->name;
        }
        @fputcsv($fp, $tmp, "\t", '"', '\\');
        $this->log('...Headers');
        $keyToName = [];
        $metadata = $dataset->getMetadataSamplesCollection($sampleSelection->selected_samples);
        $i = 0;
        $c = count($metadata);
        $this->prevPercentage = 0;
        foreach ($metadata as $meta) {
            $keyToName[$meta['key']] = [$meta['name'], $meta['id'] - 1];
            $tmp = [];
            foreach ($idx as $id) {
                $tmp[] = $meta[$id];
            }
            @fputcsv($fp, $tmp, "\t", '"', '\\');
            $this->logProgress(++$i, $c);
        }
        @fclose($fp);
        chmod($fileName, 0777);
        $sampleSelection->setMetadataFilename($fileName)->save();
        $this->log("...OK\n");
        return $keyToName;
    }

    /**
     * Count the number of available probes for this dataset
     *
     * @return mixed
     */
    protected function countProbes()
    {
        $tmp = new Probe();
        $connection = $tmp->getConnectionName();
        $collection = $tmp->getTable();
        $datasetId = $this->jobData->job_data['dataset_id'];
        return DB::connection($connection)->getCollection($collection)->count(['dataset_id' => $datasetId]);
    }

    /**
     * Build a data file
     *
     * @param SampleSelection $sampleSelection
     * @param Dataset         $dataset
     * @param array           $keyToName
     */
    protected function buildDataFile(SampleSelection $sampleSelection, Dataset $dataset, array $keyToName)
    {
        $fileName = $sampleSelection->getFileName('data', 'tsv');
        $probes = $this->countProbes();
        $this->log('Writing data file (' . $probes . ' probes)');
        $fp = fopen($fileName, 'w');
        if (!$fp) {
            throw new JobException('Unable to create data file');
        }
        $tmp = ['Probe'];
        foreach ($keyToName as $key => $dt) {
            $tmp[] = $dt[0];
        }
        @fputcsv($fp, $tmp, "\t", '"', '\\');
        $this->log('...Headers');
        $this->prevPercentage = 0;
        for ($i = 0; $i < $probes; $i++) {
            /** @var Probe $probe */
            $probe = Probe::whereDatasetId($dataset->id)->limit(1)->skip($i)->first();
            $tmp = [$probe->name];
            foreach ($keyToName as $key => $dt) {
                $key = $dt[1];
                if (!isset($probe->data[$key])) {
                    $tmp[] = "NA";
                } else {
                    $tmp[] = $probe->data[$key];
                }
            }
            @fputcsv($fp, $tmp, "\t", '"', '\\');
            $this->logProgress(($i + 1), $probes);
        }
        $this->log("...OK\n");
        @fclose($fp);
        chmod($fileName, 0777);
        $sampleSelection->setDataFilename($fileName)->save();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = $this->jobData->user;
        if ($this->attempts() > 2) {
            $this->delete();
        } else {
            $this->jobData->status = JobData::PROCESSING;
            $this->jobData->save();
            $this->sendNotification($user, 'comment',
                'One of your jobs (id: ' . $this->jobData->id . ') started processing.');
            $this->log("Starting Dataset Selection job.\n");
            $this->log('Looking for dataset');
            $dataset = Dataset::whereId($this->jobData->job_data['dataset_id'])->first();
            if ($dataset !== null) {
                $sampleSelection = null;
                try {
                    $this->log("...OK\n");
                    $sampleSelection = $this->createSampleSelection($dataset);
                    $map = $this->buildMetadataFile($sampleSelection, $dataset);
                    $this->buildDataFile($sampleSelection, $dataset, $map);
                    $this->log("Selection ready to be downloaded!\n");
                    $sampleSelection->status = SampleSelection::READY;
                    $ok = true;
                } catch (\Exception $e) {
                    $this->log("\n");
                    $errorClass = join('', array_slice(explode('\\', get_class($e)), -1));
                    $this->log('Unable to complete job. Error "' . $errorClass . '" with message "' . $e->getMessage()
                               . "\".\n");
                    $this->log('Exception trace: ' . $e->getTraceAsString());
                    if ($sampleSelection !== null && $sampleSelection instanceof SampleSelection) {
                        $sampleSelection->status = SampleSelection::FAILED;
                    }
                    $ok = false;
                }
                if ($sampleSelection !== null && $sampleSelection instanceof SampleSelection) {
                    $sampleSelection->save();
                    $tmp = $this->jobData->job_data;
                    $tmp['selection_id'] = $sampleSelection->id;
                    $this->jobData->job_data = $tmp;
                }
            } else {
                $ok = false;
                $this->log("...Failed.\nUnable to find the specified dataset.\n");
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