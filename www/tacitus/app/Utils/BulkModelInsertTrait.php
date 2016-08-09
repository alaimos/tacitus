<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Utils;


use Carbon\Carbon;

trait BulkModelInsertTrait
{

    /**
     * Insert each item as a row. Does not generate events.
     *
     * @param  array $items
     * @return bool
     */
    public function insertMany(array $items)
    {
        if (!is_array(reset($items))) {
            $items = [$items];
        }
        if ($this->timestamps) {
            $now = Carbon::now();
            array_walk($items, function (&$item) use ($now) {
                $item[self::CREATED_AT] = $now;
                $item[self::UPDATED_AT] = $now;
            });
        }
        $this->getConnection()->table($this->getTable())->insert($items);
    }

}