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
        $base = 'ftp://ftp.ncbi.nlm.nih.gov/geo/platforms/';
        if (strlen($acc) <= 6) {
            return $base . 'GPLnnn/' . $acc . '/soft/' . $acc . '_family.soft.gz';
        } elseif (strlen($acc) <= 7) {
            $nr = $acc{3};
            return $base . 'GPL' . $nr . 'nnn/' . $acc . '/soft/' . $acc . '_family.soft.gz';
        } elseif (strlen($acc) <= 8) {
            $nr = $acc{3} . $acc{4};
            return $base . 'GPL' . $nr . 'nnn/' . $acc . '/soft/' . $acc . '_family.soft.gz';
        }
        throw new ImportException('Unsupported accession number format.');
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