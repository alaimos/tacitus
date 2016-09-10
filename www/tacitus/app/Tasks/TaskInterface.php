<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Tasks;

use Illuminate\Console\Scheduling\Event;

interface TaskInterface
{

    /**
     * Schedule this command
     *
     * @param \Illuminate\Console\Scheduling\Event $event
     * @return $this
     */
    public function schedule(Event $event);

    /**
     * Runs the task
     *
     * @return $this
     */
    public function run();

    /**
     * @return \App\Models\Task
     */
    public function getModel();

}