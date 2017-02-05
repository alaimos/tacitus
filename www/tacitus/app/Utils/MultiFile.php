<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Utils;


class MultiFile
{

    const FILE_IS_GZIP  = 1;
    const FILE_IS_BZIP  = 2;
    const FILE_IS_OTHER = 0;

    /**
     * Get the mime type of a file
     *
     * @param string $file
     *
     * @return string
     */
    public static function getFileType($file)
    {
        $fInfo = new \finfo(FILEINFO_MIME_TYPE);
        return $fInfo->file($file);
    }

    /**
     * Checks if a file is zipped
     *
     * @param string $file
     *
     * @return boolean
     */
    public static function isZipped($file)
    {
        return in_array(self::getFileType($file), ['application/zip', 'application/x-zip']);
    }


    /**
     * Checks if a file is gzipped
     *
     * @param string $file
     *
     * @return boolean
     */
    public static function isGZipped($file)
    {
        return in_array(self::getFileType($file), ['application/gzip', 'application/x-gzip']);
    }

    /**
     * Checks if a file is bzipped
     *
     * @param string $file
     *
     * @return boolean
     */
    public static function isBZipped($file)
    {
        return in_array(self::getFileType($file), ['application/x-bzip2', 'application/x-bzip', 'application/bzip2',
                                                   'application/bzip']);
    }

    /**
     * Count the number of lines in a file
     *
     * @param string $file
     *
     * @return integer
     */
    public static function countLines($file)
    {
        if (self::isGZipped($file)) {
            return intval(exec('zcat ' . escapeshellarg($file) . ' | wc -l'));
        } elseif (self::isBZipped($file)) {
            return intval(exec('bzcat ' . escapeshellarg($file) . ' | wc -l'));
        } else {
            return intval(exec('wc -l ' . escapeshellarg($file)));
        }
    }

    /**
     * Opens file or URL
     *
     * @param string $file
     * @param string $mode
     *
     * @return array
     */
    public static function fileOpen($file, $mode = 'r')
    {
        if (self::isGZipped($file)) {
            return [self::FILE_IS_GZIP, @gzopen($file, $mode)];
        } elseif (self::isBZipped($file)) {
            return [self::FILE_IS_BZIP, @bzopen($file, $mode)];
        } else {
            return [self::FILE_IS_OTHER, @fopen($file, $mode)];
        }
    }

    /**
     * Checks if a pointer is correctly opened
     *
     * @param array $pointer
     *
     * @return bool
     */
    public static function fileIsOpen($pointer)
    {
        return (is_array($pointer) && count($pointer) == 2 && $pointer[1]);
    }

    /**
     * Write something to a file
     *
     * @param array    $pointer
     * @param mixed    $content
     * @param int|null $length
     *
     * @return int
     */
    public static function fileWrite($pointer, $content, $length = null)
    {
        if ($pointer[0] == self::FILE_IS_GZIP) {
            return @gzwrite($pointer[1], $content, $length);
        } elseif ($pointer[0] == self::FILE_IS_BZIP) {
            return @bzwrite($pointer[1], $content, $length);
        } else {
            return @fwrite($pointer[1], $content, $length);
        }
    }

    /**
     * Read something from a file
     *
     * @param array $pointer
     * @param int   $length
     *
     * @return int
     */
    public static function fileRead($pointer, $length)
    {
        if ($pointer[0] == self::FILE_IS_GZIP) {
            return @gzread($pointer[1], $length);
        } elseif ($pointer[0] == self::FILE_IS_BZIP) {
            return @bzread($pointer[1], $length);
        } else {
            return @fread($pointer[1], $length);
        }
    }

    /**
     * Tests for end-of-file on a file pointer
     *
     * @param array $pointer
     *
     * @return bool|int
     */
    public static function fileEOF($pointer)
    {
        if ($pointer[0] == self::FILE_IS_GZIP) {
            return @gzeof($pointer[1]);
        } else {
            return @feof($pointer[1]);
        }
    }

    /**
     * Gets line from file pointer
     *
     * @param array    $pointer
     * @param int|null $length
     *
     * @return bool|string
     */
    public static function fileReadLine($pointer, $length = null)
    {
        if ($pointer[0] == self::FILE_IS_GZIP) {
            if ($length !== null) {
                return @gzgets($pointer[1], $length);
            } else {
                return @gzgets($pointer[1]);
            }
        } elseif ($pointer[0] == self::FILE_IS_BZIP) {
            if ($length !== null) {
                return @fgets($pointer[1], $length);
            } else {
                return @fgets($pointer[1]);
            }
        } else {
            if ($length !== null) {
                return @fgets($pointer[1], $length);
            } else {
                return @fgets($pointer[1]);
            }
        }
    }

    /**
     * Closes an open file pointer
     *
     * @param array $pointer
     *
     * @return bool|int
     */
    public static function fileClose($pointer)
    {
        if ($pointer[0] == self::FILE_IS_GZIP) {
            return @gzclose($pointer[1]);
        } elseif ($pointer[0] == self::FILE_IS_BZIP) {
            return @bzclose($pointer[1]);
        } else {
            return @fclose($pointer[1]);
        }
    }

}