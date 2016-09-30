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
     * Return a renderer object for this importer
     *
     * @return \App\Platform\Import\Renderer\RendererInterface
     */
    public static function getRenderer();

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