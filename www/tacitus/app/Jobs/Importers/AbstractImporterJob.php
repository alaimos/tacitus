<?php

namespace App\Jobs\Importers;

use App\Jobs\Job;
use App\Models\Job as JobData;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class AbstractImporterJob extends Job implements ShouldQueue
{

    use InteractsWithQueue, SerializesModels;

    /**
     * @var \App\Models\Job
     */
    protected $jobData;

    /**
     * AbstractImporterJob constructor.
     *
     * @param JobData $jobData
     */
    public function __construct(JobData $jobData)
    {
        $this->jobData = $jobData;
    }

}
