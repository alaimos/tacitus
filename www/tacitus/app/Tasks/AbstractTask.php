<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Tasks;

use App\Models\Task;

abstract class AbstractTask implements TaskInterface
{
    /**
     * @var \App\Models\Task
     */
    protected $model = null;

    /**
     * Common actions to perform before task starts
     *
     * @return $this
     */
    protected function before()
    {
        $model = $this->getModel();
        $model->status = Task::RUNNING;
        $model->save();
        return $this;
    }

    /**
     * Common actions to perform after task completed
     *
     * @param bool $failed
     *
     * @return $this
     */
    protected function after($failed = false)
    {
        $model = $this->getModel();
        $model->status = ($failed) ? Task::FAILED : Task::COMPLETED;
        $model->save();
        return $this;
    }

    /**
     * Add a message to the log
     *
     * @param string $message
     * @param bool   $commit
     *
     * @return $this
     */
    protected function log($message, $commit = true)
    {
        $model = $this->getModel();
        $model->log = $model->log . $message;
        if ($commit) {
            $model->save();
        }
        return $this;
    }

    /**
     * Get a task model
     *
     * @return \App\Models\Task
     */
    public function getModel()
    {
        if ($this->model === null) {
            $this->model = new Task([
                'description' => $this->description(),
                'log'         => '',
            ]);
        }
        return $this->model;
    }

    /**
     * Returns the description of this task
     *
     * @return string
     */
    protected abstract function description();


}