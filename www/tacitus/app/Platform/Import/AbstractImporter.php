<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Platform\Import;


use App\Dataset\Traits\InteractsWithLogCallback;

abstract class AbstractImporter implements ImporterInterface
{

    use InteractsWithLogCallback;

    /**
     * Holds the last platform model imported
     *
     * @var \App\Models\Platform
     */
    protected $platform = null;

    /**
     * Holds the current user model
     *
     * @var \App\Models\User
     */
    protected $user = null;

    /**
     * Is the imported platform private?
     *
     * @var bool
     */
    protected $private = false;

    /**
     * @var integer
     */
    protected $prevPercentage;

    /**
     * Reset log progress percentage t
     *
     * @return $this
     */
    protected function resetLogProgress()
    {
        $this->prevPercentage = 0;
        return $this;
    }

    /**
     * Log progress percentage
     *
     * @param integer $current
     * @param integer $total
     */
    protected function logProgress($current, $total)
    {
        $percentage = floor(min(100, ((float)$current / (float)$total) * 100));
        if (($percentage % 10) == 0 && $percentage != 100 && $percentage != $this->prevPercentage) {
            $this->log('...' . $percentage . '%', true);
        }
        $this->prevPercentage = $percentage;
    }

    /**
     * Count the number of lines in a text file
     *
     * @param string $file
     * @return integer
     */
    protected function countLines($file)
    {
        return intval(exec('wc -l ' . escapeshellarg($file)));
    }

    /**
     * Handles setting up configuration
     *
     * @param array $config
     * @return void
     */
    protected function handleConfig(array $config)
    {
        foreach ($config as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                call_user_func([$this, $method], $value);
            }
        }
    }

    /**
     * Set if the imported platform will be private
     *
     * @param boolean $private
     * @return $this
     */
    public function setPrivate($private)
    {
        $this->private = $private;
        return $this;
    }

    /**
     * Set the current user model
     *
     * @param \App\Models\User $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get the imported platform model object
     *
     * @return \App\Models\Platform
     */
    public function getPlatform()
    {
        return $this->platform;
    }

}