<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Utils;

use Auth;

class Utils
{

    protected static $allowedForGuest = [
        'view-datasets',
    ];

    /**
     * Delete a file or a directory
     *
     * @param string $path something to delete
     * @return bool
     */
    public static function delete($path)
    {
        if (!file_exists($path)) {
            return false;
        }
        if (is_file($path)) {
            return unlink($path);
        } elseif (is_dir($path)) {
            $files = array_diff(scandir($path), ['.', '..']);
            foreach ($files as $file) {
                self::delete($path . DIRECTORY_SEPARATOR . $file);
            }
            return rmdir($path);
        }
        return false;
    }

    /**
     * Returns the size of a download
     *
     * @param string $url
     * @return int
     */
    public static function getDownloadSize($url)
    {
        if (preg_match('/ftp:\/\/([^\/]+)(.*)/i', $url, $matches)) {
            $server = $matches[1];
            $file = $matches[2];
            $ftpConnection = ftp_connect($server);
            if (!ftp_login($ftpConnection, 'anonymous', 'tacitus@user')) {
                return -1;
            }
            ftp_pasv($ftpConnection, true);
            $size = ftp_size($ftpConnection, $file);
            ftp_close($ftpConnection);
            return $size;
        } else {
            $result = -1;
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_NOBODY, true);
            curl_setopt($curl, CURLOPT_HEADER, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            $data = curl_exec($curl);
            curl_close($curl);
            if ($data) {
                $content_length = "unknown";
                $status = "unknown";
                if (preg_match('/^HTTP\/1\.[01] (\d\d\d)/', $data, $matches)) {
                    $status = (int)$matches[1];
                }
                if (preg_match('/Content-Length: (\d+)/', $data, $matches)) {
                    $content_length = (int)$matches[1];
                }
                if ($status == 200 || ($status > 300 && $status <= 308)) {
                    $result = $content_length;
                }
            }
            return (int)$result;
        }
    }

    /**
     * Returns size in a human readable format
     *
     * @param int $size
     * @return string
     */
    public static function displaySize($size)
    {
        $unit = ['b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb'];
        $i = floor(log($size, 1024));
        return @round($size / pow(1024, $i), 2) . ' ' . $unit[(int)$i];
    }

    /**
     * Checks if current user can do something
     *
     * @param string $permission
     * @return bool
     */
    public static function userCan($permission)
    {
        if (Auth::guest()) {
            return in_array($permission, self::$allowedForGuest);
        }
        return Auth::user()->can($permission);
    }

    /**
     * Get the current user
     *
     * @return \App\Models\User|null
     */
    public static function currentUser()
    {
        return Auth::guest() ? null : Auth::user();
    }

}
