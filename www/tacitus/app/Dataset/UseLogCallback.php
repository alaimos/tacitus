<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset;

/**
 * Class UseLogCallback
 * @package App\Dataset
 */
trait UseLogCallback
{

    /**
     * Log callback
     *
     * @var callable
     */
    protected $logCallback;

    /**
     * Set the logger callback
     *
     * @param callable $callback
     *
     * @return $this
     */
    public function setLogCallback(callable $callback)
    {
        $this->logCallback = $callback;
        return $this;
    }
}