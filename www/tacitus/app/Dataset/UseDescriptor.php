<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset;

/**
 * Class UseDescriptor
 * @package App\Dataset
 */
trait UseDescriptor
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

}