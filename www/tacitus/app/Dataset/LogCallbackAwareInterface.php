<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset;

use App\Models\Job as JobData;

/**
 * Interface LogCallbackAwareInterface
 *
 * @package App\Dataset
 */
interface LogCallbackAwareInterface
{

    /**
     * Set the logger callback
     *
     * @param callable $callback
     *
     * @return $this
     */
    public function setLogCallback(callable $callback);

    /**
     * Get the log callback object
     *
     * @return callable
     */
    public function getLogCallback();

    /**
     * Write a message to the log
     *
     * @param string $message
     * @return $this
     */
    public function log($message);

}