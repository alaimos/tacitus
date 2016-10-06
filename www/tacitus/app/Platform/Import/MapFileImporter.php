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
     * A MongoDb Collection where MapData will be stored
     *
     * @var \MongoDB\Collection
     */
    protected $collection;

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
        parent::__construct($mapFile);
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
            throw new ImportException("Unable to find file to import");
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
     * Import a platform
     *
     * @return $this
     */
    public function import()
    {
        $importer = new CSVFileImporter([
            'title'       => $this->title,
            'organism'    => $this->organism,
            'csvFile'     => $this->mapFile,
            'separator'   => "\t",
            'comment'     => "#",
            'identifier'  => 1,
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
     * Return a renderer object for this importer
     *
     * @return \App\Platform\Import\Renderer\RendererInterface
     */
    public static function getRenderer()
    {
        return new MapFileRenderer();
    }
}