<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Registry;


use App\Models\Source;

/**
 * Class ParserFactoryRegistry
 *
 * @package App\Dataset\Registry
 */
class ParserFactoryRegistry
{

    /**
     * A list of classes supported by the parser
     *
     * @var array
     */
    protected $classes = [];

    /**
     * A list of handlers
     *
     * @var array
     */
    protected $registeredHandlers = [];

    /**
     * ParserRegistry constructor.
     */
    public function __construct()
    {
        $this->initializeClassesList()
             ->initializeSourcesList()
             ->initializeHandlerClasses();
    }

    /**
     * Initializes the list of classes
     *
     * @return $this
     */
    private function initializeClassesList()
    {
        $path = app_path('Dataset/Factory/Parser/');
        $iterator = new \DirectoryIterator($path);
        foreach ($iterator as $file) {
            if ($file->isDot() || $file->isDir()) continue;
            $filename = $file->getFilename();
            if ($filename{0} != '.' && $file->getExtension() == 'php') {
                $className = '\App\Dataset\Factory\Parser\\' . $file->getBasename('.php');
                if (class_exists($className)) {
                    $this->classes[] = $className;
                }
            }
        }
        return $this;
    }

    /**
     * Initializes list of supported sources
     *
     * @return $this
     */
    private function initializeSourcesList()
    {
        foreach (Source::all() as $source) {
            $this->registeredHandlers[$source->name] = [];
        }
        return $this;
    }

    /**
     * Initializes handler classes
     *
     * @return $this
     */
    protected function initializeHandlerClasses()
    {
        foreach ($this->classes as $class) {
            $result = forward_static_call([$class, 'register']);
            foreach ($result as $name => $display_name) {
                if (!isset($this->registeredHandlers[$name])) {
                    $source = new Source([
                        'name'         => $name,
                        'display_name' => $display_name,
                    ]);
                    $source->save();
                    $this->registeredHandlers[$name] = [];
                }
                $this->registeredHandlers[$name][] = $class;
            }
        }
        return $this;
    }

    /**
     * Get parsers for a specific source
     *
     * @param Source|string $source
     *
     * @return \App\Dataset\Factory\ParserFactoryInterface[]|null
     */
    public function getParsers($source)
    {
        if ($source instanceof Source) {
            $source = $source->name;
        }
        if (!isset($this->registeredHandlers[$source])) {
            return null;
        }
        $parsers = [];
        foreach ($this->registeredHandlers[$source] as $class) {
            $parsers[] = new $class();
        }
        return $parsers;
    }


}