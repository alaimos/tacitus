<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Platform\Import;


use App\Dataset\Traits\InteractsWithLogCallback;
use App\Models\Platform;
use App\Platform\Import\Exception\ImportException;
use App\Utils\Exception\DownloadException;
use App\Utils\MultiFile;
use App\Utils\Utils;

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
     * Default constructor.
     *
     * @param array $config
     */
    public function __construct($config)
    {
        $this->handleConfig($config);
    }

    /**
     * Download a file from a source
     *
     * @param string $source
     * @param string $target
     * @return bool
     */
    protected function downloadFile($source, $target)
    {
        try {
            return Utils::downloadFile($source, $target, function ($target, $source, $size) {
                $displaySize = Utils::displaySize($size);
                $this->log('Downloading "' . $target . '" from "' . $source . '" (' . $displaySize . ')', true);
            }, function ($target, $source, $size) {
                $this->log("...OK\n", true);
            }, function ($target, $source, $size, $currentByte, $percentage) {
                $this->log('...' . $percentage . '%', true);
            }, function ($target, $source, $size) {
                $this->log("...Already downloaded!\n", true);
            });
        } catch (DownloadException $ex) {
            throw new ImportException($ex->getMessage(), 0, $ex);
        }
    }

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
        return MultiFile::countLines($file);
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
     * Checks and if it does not exist, creates a new platform
     *
     * @param string $title
     * @param string $organism
     * @return void
     */
    protected function checkAndCreatePlatform($title, $organism)
    {
        $found = Platform::whereTitle($title)->whereOrganism($organism)->first();
        if ($found !== null && ($found->status == Platform::READY || $found->status == Platform::PENDING)) {
            throw new ImportException('Another platform with the same name for the same organism already exists.');
        } elseif ($found !== null && $found->status == Platform::FAILED) {
            $found->delete();
        }
        $this->platform = Platform::create([
            'title'    => $title,
            'organism' => $organism,
            'private'  => $this->private,
            'user_id'  => $this->user->id,
            'status'   => Platform::PENDING,
        ]);
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