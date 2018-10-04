<?php
/**
 * Created by PhpStorm.
 * User: alaim
 * Date: 04/10/2018
 * Time: 10:19
 */

namespace App\Dataset\Uploader;


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