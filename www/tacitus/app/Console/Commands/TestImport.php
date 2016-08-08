<?php

namespace App\Console\Commands;

use App\Dataset\Descriptor;
use App\Dataset\Registry\ParserFactoryRegistry;
use App\Models\Job as JobData;
use App\Models\User;
use Illuminate\Console\Command;

class TestImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test import';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /** @var JobData $jobData */
        $jobData = JobData::findOrNew(1)->fill([
            'job_type' => 'arrexp',
            'status'   => JobData::QUEUED,
            'log'      => '',
            'job_data' => [
                'originalId' => 'E-MTAB-3732',
                'user_id'    => 1,
                'private'    => false,
            ]
        ]);
        $jobData->log = '';
        $jobData->save();
        $registry = new ParserFactoryRegistry();
        $factories = $registry->getParsers('arrexp');
        foreach ($factories as $factory) {
            $job = $factory->setJobData($jobData)->getRealImporter();
            if ($job->run()) {
                break;
            }
        }
    }
}
