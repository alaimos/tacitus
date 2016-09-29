<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Platform\Import;


abstract class AbstractImporter implements ImporterInterface
{

    /**
     * @var \App\Models\Platform
     */
    protected $platform = null;

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
     * Get the imported platform model object
     *
     * @return \App\Models\Platform
     */
    public function getPlatform()
    {
        return $this->platform;
    }

}