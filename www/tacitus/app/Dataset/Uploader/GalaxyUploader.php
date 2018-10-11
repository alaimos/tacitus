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
            $galaxy = new GalaxyInstance($credential->hostname, $credential->port, $credential->use_https);
            $galaxy->setAPIKey($credential->api_key);
            $this->log("Creating Galaxy History");
            $historyId = $this->historyCreate($galaxy, $name);
            $this->log("...OK\n");
            $tools = new \GalaxyTools($galaxy);
            $params = ['tool_id' => 'upload1', 'history_id' => $historyId];
            $this->log("Uploading data file");
            $this->realFileUpload($tools, $dataFile, $params);
            $this->log("...OK\n");
            $this->log("Uploading metadata file");
            $this->realFileUpload($tools, $metaFile, $params);
            $this->log("...OK\n");
            return true;
        }

    }

}