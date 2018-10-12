<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace {
    require_once __DIR__ . '/../../Blend/galaxy.inc';
}

namespace App\Dataset\Uploader {


    use App\Dataset\Traits\InteractsWithJobData;
    use App\Dataset\Traits\InteractsWithLogCallback;
    use App\Dataset\Uploader\Exception\UploaderException;
    use App\Models\GalaxyCredential;
    use GalaxyInstance;

    class GalaxyUploader implements UploaderInterface
    {

        use InteractsWithJobData;
        use InteractsWithLogCallback;

        /**
         * @param \GalaxyHistories $hist
         * @param string           $name
         *
         * @return null|string
         */
        private function historyLookup(\GalaxyHistories $hist, $name)
        {
            $histList = $hist->index();
            foreach ($histList as $history) {
                if ($history['name'] == $name) {
                    return $history['id'];
                }
            }
            return null;
        }

        /**
         * @param \GalaxyInstance $galaxy
         * @param  string         $name
         *
         * @return string
         */
        private function historyCreate(GalaxyInstance $galaxy, $name)
        {
            $histories = new \GalaxyHistories($galaxy);
            $old = $this->historyLookup($histories, $name);
            if ($old !== null) return $old;
            $history = $histories->create([
                'name' => $name,
            ]);
            return $history['id'];
        }

        private function realFileUpload(\GalaxyTools $tools, $filePath, $baseParams)
        {
            $baseParams['files'] = [
                0 => [
                    'name' => basename($filePath),
                    'path' => $filePath,
                    'type' => 'tsv',
                ],
            ];
            $res = $tools->create($baseParams);
            $code = $res['jobs'][0]['exit_code'];
            if ($code !== null && $code != 0) {
                throw new UploaderException("Unable to upload file");
            }
        }

        /**
         * Create the configuration file to run the preparation script
         *
         * @param string $dataFile
         * @param string $metaFile
         *
         * @return array
         */
        protected function createConfigFile($dataFile, $metaFile)
        {
            $job = $this->getJobData();
            $dir = $job->getJobDirectory();
            $config = [
                'input_data'  => $dataFile,
                'input_meta'  => $metaFile,
                'output_data' => $dir . '/' . basename($dataFile),
                'output_meta' => $dir . '/' . basename($metaFile),
            ];
            $configFile = $dir . '/config.json';
            file_put_contents($configFile, json_encode($config));
            return [$configFile, $config['output_data'], $config['output_meta']];
        }

        /**
         * Get the results of the preparation script
         *
         * @param string $fileName
         *
         * @return boolean
         */
        protected function getPreparationResult($fileName)
        {
            if (!file_exists($fileName)) {
                throw new UploaderException("Unable to find status file");
            }
            $result = json_decode(trim(file_get_contents($fileName)), true);
            @unlink($fileName);
            if (!is_array($result) || !isset($result['ok'])) {
                throw new UploaderException("Unable to complete integrator execution.");
            }
            if (!$result['ok']) {
                throw new UploaderException("Unable to complete integrator execution. Error Message: "
                                            . $result['message']);
            }
            return $result['ok'];
        }

        /**
         * Runs the preparation procedure
         *
         * @param string $dataFile
         * @param string $metaFile
         *
         * @return array|null
         */
        protected function runPreparation($dataFile, $metaFile)
        {
            $job = $this->getJobData();
            $dir = $job->getJobDirectory();
            $this->log('Running preparation procedure');
            $this->log('...Writing config file');
            list($configFile, $tmpDataFile, $tmpMetadataFile) = $this->createConfigFile($dataFile, $metaFile);
            $this->log('...Running procedure');
            $script = resource_path('scripts/prepareGalaxy.R');
            $statusFile = $dir . '/status.json';
            $command = 'Rscript ' . $script . ' -c ' . escapeshellarg($configFile) . ' -s '
                       . escapeshellarg($statusFile);
            $output = null;
            exec($command, $output);
            if ($this->getPreparationResult($statusFile)) {
                @chmod($tmpDataFile, 0777);
                @chmod($tmpMetadataFile, 0777);
                $this->log("...OK\n");
                @unlink($configFile);
                @unlink($statusFile);
                $this->log(implode("\n\t", $output) . "\n");
                return [$tmpDataFile, $tmpMetadataFile];
            }
            return ['', ''];
        }


        /**
         * Run dataset upload
         *
         * @return boolean
         * @throws \App\Dataset\Uploader\Exception\UploaderException
         */
        public function upload()
        {
            $job = $this->getJobData();
            $credId = $job->getData('credential');
            $name = $job->getData('name');
            $dataFile = $job->getData('data_file');
            $metaFile = $job->getData('metadata_file');
            $credential = GalaxyCredential::whereId($credId)->first();
            if ($credential === null || !$credential->exists) {
                throw new UploaderException('Invalid credential identifier');
            }
            if (!file_exists($dataFile)) {
                throw new UploaderException('Unable to find data file');
            }
            if (!file_exists($metaFile)) {
                throw new UploaderException('Unable to find metadata file');
            }
            if (empty($name)) {
                throw new UploaderException('A name for the uploader dataset should be specified');
            }
            list($data, $meta) = $this->runPreparation($dataFile, $metaFile);
            if (empty($data) || empty($meta)) {
                throw new UploaderException('An unknown error occurred while preparing data files');
            }
            $galaxy = new GalaxyInstance($credential->hostname, $credential->port, $credential->use_https);
            $galaxy->setAPIKey($credential->api_key);
            $this->log("Creating Galaxy History");
            $historyId = $this->historyCreate($galaxy, $name);
            $this->log("...OK\n");
            $tools = new \GalaxyTools($galaxy);
            $params = ['tool_id' => 'upload1', 'history_id' => $historyId];
            $this->log("Uploading data file");
            $this->realFileUpload($tools, $data, $params);
            $this->log("...OK\n");
            $this->log("Uploading metadata file");
            $this->realFileUpload($tools, $meta, $params);
            $this->log("...OK\n");
            @unlink($data);
            @unlink($meta);
            return true;
        }

    }

}