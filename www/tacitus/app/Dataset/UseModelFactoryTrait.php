<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset;
use App\Dataset\ModelFactory\ModelFactoryInterface;

/**
 * Class UseModelFactory
 *
 * @package App\Dataset
 */
trait UseModelFactoryTrait
{

    /**
     * Model factory
     *
     * @var \App\Dataset\ModelFactory\ModelFactoryInterface
     */
    protected $modelFactory;

    /**
     * Set a model factory object
     *
     * @param \App\Dataset\ModelFactory\ModelFactoryInterface $modelFactory
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
     * @return \App\Dataset\ModelFactory\ModelFactoryInterface
     */
    public function getModelFactory()
    {
        return $this->modelFactory;
    }


}