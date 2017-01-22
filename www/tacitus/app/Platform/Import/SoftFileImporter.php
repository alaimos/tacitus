<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Platform\Import;


use App\Models\PlatformMapData;
use App\Models\PlatformMapping;
use App\Platform\Import\Exception\ImportException;
use App\Platform\Import\Renderer\SoftFileRenderer;
use App\Utils\MultiFile;

class SoftFileImporter extends AbstractImporter implements ImporterInterface
{

    const PLATFORM_TITLE_REGEXP    = '/!Platform_title\s+=\s+(.*)/i';
    const PLATFORM_ORGANISM_REGEXP = '/!Platform_organism\s+=\s+(.*)/i';
    const TABLE_BEGIN              = '!platform_table_begin';
    const TABLE_END                = '!platform_table_end';

    /**
     * The path of a SoftFile to import
     *
     * @var string
     */
    protected $softFile;

    /**
     * When importing dataset in case of duplicate platform it will not throw an exception.
     *
     * @var boolean
     */
    protected $importingDataset;

    /**
     * A List of imported mappings
     *
     * @var array
     */
    protected $mappings = [];

    /**
     * A MongoDb Collection where MapData will be stored
     *
     * @var \MongoDB\Collection
     */
    protected $collection;

    /**
     * @return bool
     */
    public function isImportingDataset()
    {
        return $this->importingDataset;
    }

    /**
     * @param bool $importingDataset
     */
    public function setImportingDataset($importingDataset)
    {
        $this->importingDataset = $importingDataset;
    }


    /**
     * Set the name of a MapFile to import
     *
     * @param string $softFile
     *
     * @return $this
     */
    public function setSoftFile($softFile)
    {
        if (!file_exists($softFile) || !is_readable($softFile)) {
            throw new ImportException("Unable to find file to import");
        }
        $this->softFile = $softFile;
        return $this;
    }

    /**
     * Imports all mappings
     *
     * @param array $line
     *
     * @return void
     */
    protected function importMappings(array $line)
    {
        $this->mappings = [];
        for ($i = 1; $i < count($line); $i++) {
            $slug = str_slug($line[$i], '_');
            PlatformMapping::create([
                'platform_id' => $this->platform->getKey(),
                'slug'        => $slug,
                'name'        => $line[$i],
            ]);
            $this->mappings[$i] = $slug;
        }
    }

    /**
     * Import map data
     *
     * @param array $line
     *
     * @return void
     */
    protected function importMapData(array $line)
    {
        $data = [
            'platform_id' => $this->platform->getKey(),
            'probe'       => trim(stripcslashes($line[0]), '"\''),
        ];
        for ($i = 1; $i < count($line); $i++) {
            $data[$this->mappings[$i]] = trim(stripcslashes($line[$i]), '"\'');
        }
        $this->collection->insertOne($data);
    }

    /**
     * Import a platform
     *
     * @return $this
     */
    public function import()
    {
        $this->log("Importing SOFT file.\n", true);
        $fp = MultiFile::fileOpen($this->softFile, 'r');
        if (!MultiFile::fileIsOpen($fp)) {
            throw new ImportException("Unable to open file to import");
        }
        $tmp = new PlatformMapData();
        $this->collection = \DB::connection($tmp->getConnectionName())->getCollection($tmp->getTable());
        $currLineProcessed = 0;
        $totalLines = $this->countLines($this->softFile);
        $currLine = 0;
        $readingTable = false;
        $readingMeta = true;
        $title = $organism = null;
        $this->log('Reading and importing mappings', true);
        while (($line = MultiFile::fileReadLine($fp)) !== false) {
            $currLine++;
            $this->logProgress($currLine, $totalLines);
            $line = trim($line);
            if (empty($line) || $line{0} == '#') {
                continue;
            }
            if (strtolower($line) == self::TABLE_BEGIN && ($title === null || $organism === null)) {
                throw new ImportException('SOFT file is not correctly formatted: table begins before metadata are set.');
            } elseif (strtolower($line) == self::TABLE_BEGIN && $title !== null && $organism !== null) {
                try {
                    $this->checkAndCreatePlatform($title, $organism);
                } catch (ImportException $e) {
                    if ($this->importingDataset) {
                        break;
                    }
                    throw new ImportException($e->getMessage(), 0, $e);
                }
                $readingMeta = false;
                $readingTable = true;
                continue;
            } elseif (!$readingTable && strtolower($line) == self::TABLE_END) {
                throw new ImportException('SOFT file is not correctly formatted: table end found before table begin.');
            } elseif ($readingTable && strtolower($line) == self::TABLE_END) {
                $this->logProgress($totalLines, $totalLines);
                break;
            } elseif ($readingMeta) {
                if (preg_match(self::PLATFORM_TITLE_REGEXP, $line, $matches)) {
                    $title = $matches[1];
                }
                if (preg_match(self::PLATFORM_ORGANISM_REGEXP, $line, $matches)) {
                    $organism = $matches[1];
                }
            } elseif ($readingTable) {
                $line = str_getcsv($line, "\t", '"', '\\');
                if (!$currLineProcessed && count($line) <= 1) {
                    throw new ImportException("SOFT file is not correctly formatted: it should contain more than one field");
                }
                if ($currLineProcessed == 0) {
                    $this->importMappings($line);
                } else {
                    $this->importMapData($line);
                }
                $currLineProcessed++;
            }
        }
        MultiFile::fileClose($fp);
        $this->log("...OK\n", true);
        if ($this->platform !== null) {
            $this->log("The platform is now ready to use!\n");
        } else {
            $this->log("No platform table found. Nothing to import!\n");
        }
        return $this;
    }

    /**
     * Return a renderer object for this importer
     *
     * @return \App\Platform\Import\Renderer\RendererInterface
     */
    public static function getRenderer()
    {
        return new SoftFileRenderer();
    }
}