<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Traits;

use App\Dataset\Descriptor;

/**
 * Class UseDescriptor
 *
 * @package App\Dataset
 */
trait InteractsWithDescriptor
{

    /**
     * @var \App\Dataset\Descriptor
     */
    protected $descriptor;

    /**
     * Set a data descriptor object
     *
     * @param Descriptor $descriptor
     *
     * @return $this
     */
    public function setDescriptor(Descriptor $descriptor)
    {
        $this->descriptor = $descriptor;
        return $this;
    }

    /**
     * Get the data descriptor object
     *
     * @return Descriptor
     */
    public function getDescriptor()
    {
        return $this->descriptor;
    }


}