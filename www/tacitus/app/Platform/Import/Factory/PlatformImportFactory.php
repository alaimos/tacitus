<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Platform\Import\Factory;

use App\Platform\Import\Factory\Exception\FactoryException;
use App\Platform\Import\Renderer\RendererInterface;

/**
 * Class PlatformImportFactory
 *
 * @package App\Platform\Import\Factory
 */
final class PlatformImportFactory
{

    /**
     * A list of supported importers
     *
     * @var array
     */
    protected $classes = [
        \App\Platform\Import\MapFileImporter::class,
        \App\Platform\Import\SoftFileImporter::class,
        \App\Platform\Import\GEOPlatformImporter::class,
    ];

    /**
     * A list of renderers
     *
     * @var RendererInterface[]
     */
    protected $registeredRenderers = [];

    /**
     * A map from importer name to its class
     *
     * @var array
     */
    protected $importerNameToClassMap = [];

    /**
     * A list of importers for the web interface
     *
     * @var array
     */
    protected $importersList = [];

    /**
     * ParserRegistry constructor.
     */
    public function __construct()
    {
        $this->setup();
    }

    /**
     * Setup this object
     */
    protected function setup()
    {
        foreach ($this->classes as $class) {
            /** @var RendererInterface $renderer */
            $renderer = $class::getRenderer();
            list($name, $description) = $renderer->getImporterDescription();
            $this->importersList[$name] = $description;
            $this->importerNameToClassMap[$name] = $class;
            $this->registeredRenderers[$name] = $renderer;
        }
    }

    /**
     * Returns the list of supported importers
     *
     * @return array
     */
    public function getImportersList()
    {
        return $this->importersList;
    }

    /**
     * Instantiate a new importer
     *
     * @param string $name
     * @param array  $config
     * @return \App\Platform\Import\ImporterInterface
     */
    public function getImporter($name, array $config)
    {
        if (!isset($this->importersList[$name])) {
            throw new FactoryException('The specified importer does not appear to exist.');
        }
        $class = $this->importerNameToClassMap[$name];
        return new $class($config);
    }

    /**
     * Get a renderer
     *
     * @param string $name
     * @return \App\Platform\Import\Renderer\RendererInterface
     */
    public function getRenderer($name)
    {
        if (!isset($this->importersList[$name])) {
            throw new FactoryException('The specified importer does not appear to exist.');
        }
        return $this->registeredRenderers[$name];
    }

}