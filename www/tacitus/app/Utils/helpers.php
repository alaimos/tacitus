<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

use App\Utils\Utils;

if (!function_exists('user_can')) {
    function user_can($permission)
    {
        return Utils::userCan($permission);
    }
}

if (!function_exists('current_user')) {
    function current_user()
    {
        return Utils::currentUser();
    }
}