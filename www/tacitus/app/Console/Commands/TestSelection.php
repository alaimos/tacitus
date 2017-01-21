<?php

namespace App\Console\Commands;

use App\Models\Dataset;
use App\Models\Job as JobData;
use App\Models\Probe;
use App\Models\Sample;
use DB;
use Illuminate\Console\Command;

class TestSelection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:selection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test selection';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $jobData = JobData::whereId(6)->first();
        $samples = $jobData->job_data['samples'];

        $q = Sample::where(function (\Jenssegers\Mongodb\Eloquent\Builder $query) use ($samples) {
            foreach ($samples as $sample) {
                $query->orWhere('_id', '=', $sample);
            }
        })->get();
        /** @var Dataset $dataset */
        //$dataset = Dataset::whereId($jobData->job_data['dataset_id'])->first();
        $count = DB::connection('mongodb')->getCollection('probes')
                   ->count(['dataset_id' => $jobData->job_data['dataset_id']]);
        echo $count . "\n";
        $times = [];
        for ($i = 0; $i < $count; $i++) {
            $t = microtime(true);
            $probe = Probe::whereDatasetId($jobData->job_data['dataset_id'])->limit(1)->skip($i)->first();
            /*DB::connection('mongodb')->collection('probes')->where('dataset_id', '=',
                $jobData->job_data['dataset_id'])->limit(1)->skip($i)->first();*/
            $times[] = microtime(true) - $t;
            if (($i % 1000) === 0) {
                echo $i . "...";
            }
        }
        echo "\n\n";
        echo min($times) . " - " . (array_sum($times) / count($times)) . " - " . max($times);
    }
}
