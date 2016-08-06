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
     * @param bool   $autoCommit
     * @return $this
     */
    public function log($message, $autoCommit = false)
    {
        if (is_callable($this->logCallback)) {
            call_user_func($this->logCallback, $message, $autoCommit);
        }
        return $this;
    }


}