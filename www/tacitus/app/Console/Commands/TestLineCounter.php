<?php

namespace App\Console\Commands;

use App\Dataset\Descriptor;
use App\Dataset\Registry\ParserFactoryRegistry;
use App\Models\Job as JobData;
use App\Models\User;
use App\Utils\Bench;
use Illuminate\Console\Command;
use SplFileObject;

class TestLineCounter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:counter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test line counter';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $file = '/var/www/tacitus/storage/app/jobs/1/E-MTAB-3732.processed.1.zip_out/processedMatrix.Aurora.july2015.txt';
        echo escapeshellarg($file) . "\n\n";
        $firstMethod = function () use ($file) {
            $f = fopen($file, 'rb');
            $lines = 0;
            while (!feof($f)) {
                $lines += substr_count(fread($f, 8192), "\n");
            }
            fclose($f);
            echo "..." . $lines . "...";
        };
        $secondMethod = function () use ($file) {
            $count = 0;
            $file = new SplFileObject($file);
            $file->seek($file->getSize());
            $count = $file->key();
            $file = null;
            echo "..." . $count . "...";
        };
        $thirdMethod = function () use ($file) {
            $handle = fopen($file, "r");
            $lines = 0;
            while (!feof($handle)) {
                fgets($handle);
                $lines++;

            }
            fclose($handle);
            echo "..." . ($lines - 1) . "...";
        };
        $reference = function () use ($file) {
            $result = exec('wc -l ' . escapeshellarg($file));
            echo "..." . (intval($result)) . "...";
        };
        $bench = new Bench(3);
        $bench->addBench('First', $firstMethod)
            ->addBench('Second', $secondMethod)
            ->addBench('Third', $thirdMethod)
            ->addBench('Reference', $reference);
        $bench->run();
        echo $bench;
    }
}
