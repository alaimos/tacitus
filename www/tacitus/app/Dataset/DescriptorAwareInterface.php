<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset;

/**
 * Interface DescriptorAwareInterface
 *
 * @package App\Dataset
 */
interface DescriptorAwareInterface
{

    /**
     * Set a data descriptor object
     *
     * @param Descriptor $descriptor
     *
     * @return $this
     */
    public function setDescriptor(Descriptor $descriptor);

    /**
     * Get the data descriptor object
     *
     * @return Descriptor
     */
    public function getDescriptor();

}