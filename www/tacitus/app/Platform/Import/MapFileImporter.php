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
use App\Platform\Import\Renderer\MapFileRenderer;
use App\Utils\MultiFile;

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
     * @param boolean|null $private
     */
    public function __construct($mapFile, $title = null, $organism = null, $private = null)
    {
        if (!is_array($mapFile)) {
            $mapFile = compact('mapFile', 'title', 'organism', 'private');
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
        if (empty($organism)) {
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
        $mapFrom = trim(stripcslashes($line[0]), '"\'');
        for ($i = 1; $i < count($line); $i++) {
            $mapTo = trim(stripcslashes($line[$i]), '"\'');
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
        $this->log('Importing new platform "' . $this->title . "\".\n", true);
        $this->platform = Platform::create([
            'title'    => $this->title,
            'organism' => $this->organism,
            'private'  => $this->private,
            'user_id'  => $this->user->id,
        ]);
        $currLineProcessed = 0;
        $totalLines = $this->countLines($this->mapFile);
        $currLine = 0;
        $fp = MultiFile::fileOpen($this->mapFile, 'r');
        if (!MultiFile::fileIsOpen($fp)) {
            throw new \RuntimeException("Unable to open file to import");
        }
        $this->resetLogProgress();
        $this->log('Importing mappings', true);
        while (($line = MultiFile::fileReadLine($fp)) !== false) {
            $currLine++;
            $this->logProgress($currLine, $totalLines);
            $line = trim($line);
            if (empty($line) || $line{0} == '#') { //ignores empty lines or commented lines
                continue;
            }
            $line = explode("\t", $line);
            if (!$currLineProcessed && count($line) <= 1) {
                throw new \RuntimeException("The Map File should contain more than one field");
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
        return new MapFileRenderer();
    }
}