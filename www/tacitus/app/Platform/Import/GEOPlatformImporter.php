<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Platform\Import;

use App\Platform\Import\Exception\ImportException;
use App\Platform\Import\Renderer\GEOPlatformRenderer;

class GEOPlatformImporter extends AbstractImporter implements ImporterInterface
{

    /**
     * Pattern used to build the URL for the download of the GPL SOFT file
     */
    const GPL_SOFT_URL = 'https://ftp.ncbi.nlm.nih.gov/geo/platforms/%s/%s/soft/%s';

    /**
     * Pattern used to build the GSE SOFT filename
     */
    const GPL_SOFT_FILENAME = '%s_family.soft.gz';

    /**
     * Pattern used to build URL prefix
     */
    const PREFIX_REGEXP = '/\\d{1,3}$/';

    /**
     * Replacement for the URL prefix
     */
    const PREFIX_REPLACEMENT = 'nnn';


    /**
     * The accession number of the GEO platform
     *
     * @var string
     */
    protected $accessionNumber;

    /**
     * The directory where all files will be downloaded
     *
     * @var string
     */
    protected $downloadDirectory;

    /**
     * Set the accession number of the GEO platform
     *
     * @param string $accessionNumber
     *
     * @return $this
     */
    public function setAccessionNumber($accessionNumber)
    {
        if (!preg_match('/^GPL([0-9]+)/i', $accessionNumber)) {
            throw  new ImportException('Invalid accession number.');
        }
        $this->accessionNumber = $accessionNumber;
        return $this;
    }

    /**
     * Set the download directory where all files will be stored
     *
     * @param string $downloadDirectory
     *
     * @return $this
     */
    public function setDownloadDirectory($downloadDirectory)
    {
        if (!is_dir($downloadDirectory) || !is_writable($downloadDirectory)) {
            throw new ImportException('Invalid download directory.');
        }
        $this->downloadDirectory = $downloadDirectory;
        return $this;
    }

    /**
     * Import a platform
     *
     * @return $this
     */
    public function import()
    {
        $this->log("Importing GEO Platform \"" . $this->accessionNumber . "\" file.\n", true);
        $downloadUrl = $this->getDownloadUrl();
        $targetFile = $this->downloadDirectory . '/' . $this->accessionNumber . '_family.soft.gz';
        $this->downloadFile($downloadUrl, $targetFile);
        $importer = new SoftFileImporter([
            'softFile'    => $targetFile,
            'private'     => $this->private,
            'user'        => $this->user,
            'logCallback' => $this->logCallback,
        ]);
        $toThrow = null;
        try {
            $importer->import();
        } catch (\Exception $exception) {
            $toThrow = $exception;
        }
        $this->platform = $importer->getPlatform();
        if ($toThrow !== null) {
            throw new ImportException($toThrow->getMessage(), 0, $toThrow);
        }
        return $this;
    }

    /**
     * Generate download URL on the basis of GEO rules
     *
     * @return string
     */
    protected function getDownloadUrl()
    {
        $acc = strtoupper($this->accessionNumber);
        $prefix = preg_replace(self::PREFIX_REGEXP, self::PREFIX_REPLACEMENT, $acc);
        $fileName = sprintf(self::GPL_SOFT_FILENAME, $acc);
        $url = sprintf(self::GPL_SOFT_URL, $prefix, $acc, $fileName);
        return $url;
    }

    /**
     * Return a renderer object for this importer
     *
     * @return \App\Platform\Import\Renderer\RendererInterface
     */
    public static function getRenderer()
    {
        return new GEOPlatformRenderer();
    }
}