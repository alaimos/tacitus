<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Traits;

use App\Dataset\Factory\ModelFactoryInterface;

/**
 * Class UseModelFactory
 *
 * @package App\Dataset
 */
trait InteractsWithModelFactory
{

    /**
     * Model factory
     *
     * @var \App\Dataset\Factory\ModelFactoryInterface
     */
    protected $modelFactory;

    /**
     * Set a model factory object
     *
     * @param \App\Dataset\Factory\ModelFactoryInterface $modelFactory
     *
     * @return $this
     */
    public function setModelFactory(ModelFactoryInterface $modelFactory)
    {
        $this->modelFactory = $modelFactory;
        return $this;
    }

    /**
     * Get the model factory object
     *
     * @return \App\Dataset\Factory\ModelFactoryInterface
     */
    public function getModelFactory()
    {
        return $this->modelFactory;
    }


}