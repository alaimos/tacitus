<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Utils;

/**
 * Interface BulkInsertableInterface
 *
 * @package App\Utils
 */
interface BulkInsertableInterface
{

    /**
     * Insert each item as a row. Does not generate events.
     *
     * @param  array $items
     *
     * @return bool
     */
    public function insertMany(array $items);

}