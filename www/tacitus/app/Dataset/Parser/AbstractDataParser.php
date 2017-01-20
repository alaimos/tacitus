<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Parser;

use App\Dataset\Parser\Exception\DataParserException;
use App\Dataset\Traits\InteractsWithDescriptor;

/**
 * Class AbstractDataParser
 *
 * @package App\Dataset\Parser
 */
abstract class AbstractDataParser implements DataParserInterface
{

    use InteractsWithDescriptor;

    /**
     * The current type of files being parsed
     *
     * @var null|string
     */
    protected $currentType = null;

    /**
     * A list of files to parse
     *
     * @var null|array
     */
    protected $files = null;

    /**
     * The total number of line to parse
     *
     * @var null|integer
     */
    protected $totalCount = null;

    /**
     * The file that is being parsed
     *
     * @var null|string
     */
    protected $currentFile = null;

    /**
     * The current index of the line being parsed
     *
     * @var null|integer
     */
    protected $currentIndex = null;

    /**
     * Signal to skip to the next file even if the current is not completed
     *
     * @var bool
     */
    protected $skipToNextFile = false;

    /**
     * Signal parser to skip the first line of each file
     *
     * @var bool
     */
    protected $skipFirstLine = false;

    /**
     * The current file pointer
     *
     * @var null|resource
     */
    protected $currentFilePointer = null;

    /**
     * Count the number of lines in a text file
     *
     * @param string $file
     * @return integer
     */
    protected function countLines($file)
    {
        return intval(exec('wc -l ' . escapeshellarg($file)));
        /*$f = fopen($file, 'rb');
        $lines = 0;
        while (!feof($f)) {
            $lines += substr_count(fread($f, 8192), "\n");
        }
        fclose($f);
        if ($lines > 0 && $this->skipFirstLine) {
            $lines -= 1;
        }
        return $lines;*/
    }

    /**
     * Internal method to set the current type in order to use a fluent interface
     *
     * @param string $type
     * @return $this
     */
    protected function setCurrentType($type)
    {
        $this->currentType = $type;
        return $this;
    }

    /**
     * Initializes files list with valid files
     *
     * @return $this
     */
    protected function initFilesList()
    {
        $tmp = $this->getDescriptor()->getFiles($this->currentType);
        $this->files = [];
        if (is_array($tmp)) {
            foreach ($tmp as $file) {
                if (!empty($file) && file_exists($file) && is_readable($file)) {
                    $this->files[] = $file;
                }
            }
        }
        if (empty($this->files)) {
            $this->files = null;
        }
        return $this;
    }

    /**
     * Checks if there is at least one file to parse
     *
     * @return $this
     */
    protected function checkFiles()
    {
        if ($this->files === null || empty($this->files)) {
            throw new DataParserException('No files to parse for type "' . $this->currentType . '".');
        }
        return $this;
    }

    /**
     * Initializes the counter of elements to parse
     *
     * @return $this
     */
    protected function initCounter()
    {
        $this->totalCount = 0;
        foreach ($this->files as $file) {
            $this->totalCount += $this->countLines($file);
        }
        $this->currentIndex = 0;
        return $this;
    }

    /**
     * Closes the current file pointer
     *
     * @return $this
     */
    protected function closeCurrentFilePointer()
    {
        if ($this->currentFilePointer !== null && is_resource($this->currentFilePointer)) {
            @fclose($this->currentFilePointer);
        }
        $this->skipToNextFile = false;
        $this->currentFilePointer = null;
        return $this;
    }

    /**
     * Resets parser
     *
     * @return $this
     */
    protected function reset()
    {
        $this->closeCurrentFilePointer();
        $this->currentType = $this->totalCount = $this->currentFile = $this->currentIndex = null;
        return $this;
    }

    /**
     * Get the current file pointer
     *
     * @return null|resource
     */
    protected function getFilePointer()
    {
        if ($this->currentFilePointer !== null && is_resource($this->currentFilePointer)
            && !feof($this->currentFilePointer)
            && !$this->skipToNextFile
        ) {
            return $this->currentFilePointer;
        } elseif (!empty($this->files)) {
            $this->closeCurrentFilePointer();
            $this->currentFile = array_shift($this->files);
            $this->currentFilePointer = fopen($this->currentFile, 'r');
            if (!$this->currentFilePointer) {
                throw new DataParserException('Unable to open file "' . $this->currentFile . '".');
            }
            if ($this->skipFirstLine) {
                fgets($this->currentFilePointer);
            }
            return $this->currentFilePointer;
        }
        return null;
    }

    /**
     * Initializes the parsing of all data files associated with a specific type
     *
     * @param string $type
     * @return \App\Dataset\Parser\DataParserInterface
     * @throws \App\Dataset\Parser\Exception\DataParserException
     */
    public function start($type)
    {
        return $this->reset()->setCurrentType($type)->initFilesList()->checkFiles()->initCounter();
    }

    /**
     * Parse one element. This function returns something until all the files have been parsed.
     * A null output occurs when nothing to parse remain.
     *
     * @return mixed|null
     * @throws \App\Dataset\Parser\Exception\DataParserException
     */
    public function parse()
    {
        $fp = $this->getFilePointer();
        if ($fp) {
            $row = fgets($fp);
            if ($row !== false) {
                $this->currentIndex++;
                return $this->parser(trim($row));
            }
        }
        return null;
    }

    /**
     * The total number of elements to parse in the current type or null if no element is being parsed.
     *
     * @return integer|null
     */
    public function count()
    {
        return $this->totalCount;
    }

    /**
     * The index of the current element being parsed or null if no element is being parsed.
     *
     * @return integer|null
     */
    public function current()
    {
        return $this->currentIndex;
    }

    /**
     * The real parser implementation
     *
     * @param string $row
     * @return mixed
     * @throws \App\Dataset\Parser\Exception\DataParserException
     */
    protected abstract function parser($row);

    /**
     * Class Destructor
     *
     * @return void
     */
    function __destruct()
    {
        $this->closeCurrentFilePointer();
    }


}