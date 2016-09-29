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

class MapFileImporter extends AbstractImporter implements ImporterInterface
{

    /**
     * The path of a MapFile to import
     *
     * @var string
     */
    protected $mapFile;

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
     * A List of imported mappings
     *
     * @var array
     */
    protected $mappings = [];

    /**
     * MapFileImporter constructor.
     *
     * @param string|array $mapFile
     * @param string|null  $title
     * @param string|null  $organism
     */
    public function __construct($mapFile, $title = null, $organism = null)
    {
        if (!is_array($mapFile)) {
            $mapFile = compact('mapFile', 'title', 'organism');
        }
        $this->handleConfig($mapFile);
    }

    /**
     * Set the name of a MapFile to import
     *
     * @param string $mapFile
     * @return $this
     */
    public function setMapFile($mapFile)
    {
        if (!file_exists($mapFile) || !is_readable($mapFile)) {
            throw new \RuntimeException("Unable to find file to import");
        }
        $this->mapFile = $mapFile;
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
            throw new \RuntimeException("The title is required");
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
        if (empty($title)) {
            throw new \RuntimeException("The organism is required");
        }
        $this->organism = $organism;
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
        $this->mappings = [];
        for ($i = 1; $i < count($line); $i++) {
            $mapping = PlatformMapping::create([
                'platform_id' => $this->platform->getKey(),
                'name'        => $line[$i],
            ]);
            $this->mappings[$i] = $mapping->getKey();
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
        $mapFrom = $line[0];
        for ($i = 1; $i < count($line); $i++) {
            $mapTo = $line[$i];
            if (empty($mapTo)) {
                continue;
            }
            PlatformMapData::create([
                'platform_id' => $this->platform->getKey(),
                'mapping_id'  => $this->mappings[$i],
                'mapFrom'     => $mapFrom,
                'mapTo'       => $mapTo,
            ]);
        }
    }


    /**
     * Import a platform
     *
     * @return $this
     */
    public function import()
    {
        $this->platform = new Platform([
            'title'    => $this->title,
            'organism' => $this->organism,
        ]);
        $this->platform->save();
        $currLine = 0;
        $fp = @fopen($this->mapFile, 'r');
        if (!$fp) {
            throw new \RuntimeException("Unable to open file to import");
        }
        while (($line = @fgets($fp)) !== false) {
            $line = trim($line);
            if (empty($line) || $line{0} == '#') { //ignores empty lines or commented lines
                continue;
            }
            $line = explode("\t", $line);
            if (!$currLine && count($line) <= 1) {
                throw new \RuntimeException("The Map File should contain more than one field");
            }
            if ($currLine == 0) {
                $this->importMappings($line);
            } else {
                $this->importMapData($line);
            }
            $currLine++;
        }
        @fclose($fp);
        return $this;
    }


}