<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset;

/**
 * Class UseLogCallback
 *
 * @package App\Dataset
 */
trait UseLogCallbackTrait
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

    /**
     * Get the log callback object
     *
     * @return callable
     */
    public function getLogCallback()
    {
        return $this->logCallback;
    }

    /**
     * Write a message to the log
     *
     * @param string $message
     * @return $this
     */
    public function log($message)
    {
        if (is_callable($this->logCallback)) {
            call_user_func($this->logCallback, $message);
        }
        return $this;
    }


}