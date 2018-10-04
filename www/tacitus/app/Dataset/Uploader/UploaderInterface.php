<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Uploader;


use App\Dataset\Contracts\JobDataAwareInterface;
use App\Dataset\Contracts\LogCallbackAwareInterface;

interface UploaderInterface extends JobDataAwareInterface, LogCallbackAwareInterface
{

    /**
     * Run dataset upload
     *
     * @return boolean
     * @throws \App\Dataset\Uploader\Exception\UploaderException
     */
    public function upload();


}