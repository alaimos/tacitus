<?php

namespace app\Utils;


class Utils
{

    /**
     * @param string        $source
     * @param string        $target
     * @param null|callable $outputCallback
     * @return bool
     */
    public static function download($source, $target, $outputCallback = null)
    {
        $rh = fopen($source, 'rb');
        $wh = fopen($target, 'w+b');
        if (!$rh || !$wh) {
            return false;
        }
        $prev = 'Downloading';
        $i = 1;
        while (!feof($rh)) {
            if (fwrite($wh, fread($rh, 4096)) === false) {
                return false;
            }
            if (is_callable($outputCallback)) {
                if (($i % 3) == 0) {
                    $prev = 'Downloading';
                    $i = 1;
                } else {
                    $prev .= '.';
                    $i++;
                }
                $outputCallback($prev . "\r");
            }
            flush();
        }
        $outputCallback('\n');
        fclose($rh);
        fclose($wh);
        return true;
    }

}