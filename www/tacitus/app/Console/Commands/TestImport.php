<?php

namespace App\Console\Commands;

use App\Dataset\Descriptor;
use App\Dataset\Registry\ParserFactoryRegistry;
use App\Models\Job as JobData;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class TestImport extends Command
{

    use DispatchesJobs;

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
        $jobData = JobData::findOrNew(50)->fill([
            'job_type' => 'import_dataset',
            'status'   => JobData::QUEUED,
            'log'      => '',
            'user_id'  => 1,
            'job_data' => [
                'source_type' => 'geogse',
                'original_id' => 'GSE30611', //'GSE14',
                'private'     => false,
            ],
        ]);
        $jobData->log = '';
        $jobData->save();

        /*$this->dispatch(Factory::getQueueJob($jobData));*/

        $registry = new ParserFactoryRegistry();
        /** @var \App\Dataset\Factory\ParserFactoryInterface[] $factories */
        $factories = $registry->getParsers('geogse');
        $jobData->status = JobData::PROCESSING;
        $jobData->save();
        /** @var \App\Dataset\Factory\ParserFactoryInterface $factory */
        $factory = array_shift($factories);
        $factory->setJobData($jobData);
        $importer = $factory->getRealImporter();
        $importer->run();
        return 0;

        /*$downloader = $factory->getDatasetDownloader();
        $downloader->setDownloadDirectory($jobData->getJobDirectory());
        $descriptor = $downloader->download();
        dump($descriptor);
        $factory->setDescriptor($descriptor);
        $parser = $factory->getDataParser();
        $parser->start(Descriptor::TYPE_METADATA_INDEX);
        $first = false;
        echo "Metadata index";
        while (($res = $parser->parse()) !== null) {
            if (!$first && $res) {
                dump($res);
                $first = true;
            }
            echo ".";
            //echo $parser->current() . " of " . $parser->count() . " - " . ($res ? "sample" : "false") . "\n";
        }
        echo "\n";
        $parser->start(Descriptor::TYPE_METADATA);
        //echo $parser->current() . " of " . $parser->count() . "\n";
        $first = false;
        while (($res = $parser->parse()) !== null) {
            if ($res) {
                dd($res);
            }
            if (!$first && $res) {
                dump($res);
                $first = true;
            }
            echo $parser->current() . " of " . $parser->count() . " - " . ($res ? "sample" : "false") . "\n";
        }
        /*$parser->start(Descriptor::TYPE_DATA);
        echo $parser->current() . " of " . $parser->count() . "\n";
        while (($res = $parser->parse()) !== null) {
            echo $parser->current() . " of " . $parser->count() . " - " . ($res ? "sample" : "false") . "\n";
            /*if ($res) {
                  dd($res);
            }*/
        //}
        //dd($res);
        /*$ok = false;
        foreach ($factories as $factory) {
            $job = $factory->setJobData($jobData)->getRealImporter();
            if ($job->run()) {
                $ok = true;
                break;
            }
        }
        if ($ok) {
            $jobData->status = JobData::COMPLETED;
        } else {
            $jobData->status = JobData::FAILED;
        }*/
        return 0;
    }
}
