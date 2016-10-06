<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Platform\Import;


use App\Models\Platform;
use App\Models\PlatformMapData;
use App\Models\PlatformMapping;
use App\Platform\Import\Exception\ImportException;
use App\Platform\Import\Renderer\CSVFileRenderer;
use App\Platform\Import\Renderer\MapFileRenderer;
use App\Utils\MultiFile;

class CSVFileImporter extends AbstractImporter implements ImporterInterface
{

    /**
     * The title of this platform
     *
     * @var string
     */
    protected $title;

    /**
     * The organism of this platform
     *
     * @var string
     */
    protected $organism;

    /**
     * The name of the CSV file that will be imported
     *
     * @var string
     */
    protected $csvFile;

    /**
     * The separator character used in the CSV file
     *
     * @var string
     */
    protected $separator = ',';

    /**
     * The comment character used in the CSV file
     *
     * @var string
     */
    protected $comment = '#';

    /**
     * The position (starting from 1) of the identifier field
     *
     * @var int
     */
    protected $identifier = 1;

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
     * Set the name of a CSV file to import
     *
     * @param string $csvFile
     * @return $this
     */
    public function setCsvFile($csvFile)
    {
        if (!file_exists($csvFile) || !is_readable($csvFile)) {
            throw new ImportException("Unable to find file to import");
        }
        $this->csvFile = $csvFile;
        return $this;
    }

    /**
     * Set the title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        if (empty($title)) {
            throw new ImportException("The title is required");
        }
        $this->title = $title;
        return $this;
    }

    /**
     * Set the organism
     *
     * @param string $organism
     * @return $this
     */
    public function setOrganism($organism)
    {
        if (empty($organism)) {
            throw new ImportException("The organism is required");
        }
        $this->organism = $organism;
        return $this;
    }

    /**
     * Set the separator character
     *
     * @param string $separator
     * @return $this
     */
    public function setSeparator($separator)
    {
        if (empty($separator)) {
            throw new ImportException("The separator character is required");
        }
        $this->separator = $separator;
        return $this;
    }

    /**
     * Set the comment character
     *
     * @param string $comment
     * @return $this
     */
    public function setComment($comment)
    {
        if (empty($comment)) {
            throw new ImportException("The comment character is required");
        }
        $this->comment = $comment;
        return $this;
    }

    /**
     * Set the identifier field number
     *
     * @param int $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $identifier = (int)$identifier;
        if (empty($identifier) || $identifier <= 0) {
            throw new ImportException("The identifier field number is required");
        }
        $this->identifier = (int)$identifier;
        return $this;
    }

    /**
     * Imports all mappings
     *
     * @param array $line
     * @return void
     */
    protected function importMappings(array $line)
    {
        $idField = $this->identifier - 1;
        if ($idField < 0 || $idField >= count($line)) {
            throw new ImportException('Invalid identifier field position: position is out of range.');
        }
        $this->mappings = [];
        for ($i = 0; $i < count($line); $i++) {
            if ($i == $idField) {
                continue;
            }
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
     * @return void
     */
    protected function importMapData(array $line)
    {
        $unquote = function ($str) {
            return trim(stripcslashes($str), '"\'');
        };
        $idField = $this->identifier - 1;
        if ($idField < 0 || $idField >= count($line)) {
            throw new ImportException('Invalid identifier field position: position is out of range.');
        }
        $idValue = $line[$idField];
        $data = [
            'platform_id' => $this->platform->getKey(),
            'probe'       => $unquote($idValue),
        ];
        for ($i = 0; $i < count($line); $i++) {
            if ($i == $idField) {
                continue;
            }
            if (!isset($this->mappings[$i])) {
                dd($line);
            }
            $data[$this->mappings[$i]] = $unquote($line[$i]);
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
        $this->log('Importing new platform "' . $this->title . "\".\n", true);
        $this->checkAndCreatePlatform($this->title, $this->organism);
        $tmp = new PlatformMapData();
        $this->collection = \DB::connection($tmp->getConnectionName())->getCollection($tmp->getTable());
        $currLineProcessed = 0;
        $totalLines = $this->countLines($this->csvFile);
        $currLine = 0;
        $fp = MultiFile::fileOpen($this->csvFile, 'r');
        if (!MultiFile::fileIsOpen($fp)) {
            throw new ImportException("Unable to open file to import");
        }
        $this->resetLogProgress();
        $this->log('Importing mappings', true);
        while (($line = MultiFile::fileReadLine($fp)) !== false) {
            $currLine++;
            $this->logProgress($currLine, $totalLines);
            $line = trim($line);
            if (empty($line) || $line{0} == $this->comment) { //ignores empty lines or commented lines
                continue;
            }
            $line = str_getcsv($line, $this->separator, '"', '\\');
            if (!$currLineProcessed && count($line) <= 1) {
                throw new ImportException("The file should contain more than one field");
            }
            if ($currLineProcessed == 0) {
                $this->importMappings($line);
            } else {
                $this->importMapData($line);
            }
            $currLineProcessed++;
        }
        $this->log("...OK\n", true);
        $this->log("The platform is now ready to use!\n");
        MultiFile::fileClose($fp);
        return $this;
    }

    /**
     * Return a renderer object for this importer
     *
     * @return \App\Platform\Import\Renderer\RendererInterface
     */
    public static function getRenderer()
    {
        return new CSVFileRenderer();
    }
}