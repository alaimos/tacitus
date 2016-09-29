<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Platform\Import;


interface ImporterInterface
{

    /**
     * Get the imported platform model object
     *
     * @return \App\Models\Platform
     */
    public function getPlatform();

    /**
     * Import a platform
     *
     * @return $this
     */
    public function import();

}