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

    class GalaxyUploader implements UploaderInterface
    {

        use InteractsWithJobData;
        use InteractsWithLogCallback;

        /**
         * Run dataset upload
         *
         * @return boolean
         * @throws \App\Dataset\Uploader\Exception\UploaderException
         */
        public function upload()
        {
            // TODO: Implement upload() method.
        }

    }

}