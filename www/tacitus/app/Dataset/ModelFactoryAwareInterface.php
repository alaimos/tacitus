<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset;

use App\Dataset\ModelFactory\ModelFactoryInterface;


/**
 * Interface ModelFactoryAwareInterface
 *
 * @package App\Dataset
 */
interface ModelFactoryAwareInterface
{

    /**
     * Set a model factory object
     *
     * @param \App\Dataset\ModelFactory\ModelFactoryInterface $modelFactory
     * @return $this
     */
    public function setModelFactory(ModelFactoryInterface $modelFactory);

    /**
     * Get the model factory object
     *
     * @return \App\Dataset\ModelFactory\ModelFactoryInterface
     */
    public function getModelFactory();

}