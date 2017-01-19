<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Parser;

use App\Dataset\Descriptor;
use App\Dataset\Parser\Exception\DataParserException;

/**
 * Class GeoGSEDataParser
 *
 * @package App\Dataset\Parser
 */
class GeoGSEDataParser extends AbstractDataParser
{

    /**
     * Regular expression to check for metadata sample begin
     */
    const METADATA_SAMPLE_BEGIN = '/^\\^sample\s+=\s+(.*)$/i';

    /**
     * Regular expression to check for metadata sample end
     */
    const METADATA_SAMPLE_END = '/^!sample_table_end$/i';

    /**
     * Regular expression to extract data from a metadata sample row
     */
    const METADATA_SAMPLE_ROW = '/!sample_(\S+)\s+=\s+(.*)/i';

    /**
     * Regular expression to check for series table begin
     */
    const SERIES_MATRIX_BEGIN = '/^!series_matrix_table_begin$/i';

    /**
     * Regular expression to check for series matrix end
     */
    const SERIES_MATRIX_END = '/^!series_matrix_table_end$/i';

    /**
     * Index used to find identifier position
     *
     * @var int|null
     */
    protected $identifierIndex = null;

    /**
     * Index which stores the list of all metadata
     *
     * @var array|null
     */
    protected $metadataIndex = [];

    /**
     * Index used to find samples associated with probes
     *
     * @var array|null
     */
    protected $sampleIndex = null;

    /**
     * The number of the last sample
     *
     * @var integer|null
     */
    protected $lastSample = null;

    /**
     * Is parser inside series matrix?
     *
     * @var bool
     */
    protected $inSeriesMatrix = false;

    /**
     * Internal method to set the current type in order to use a fluent interface
     *
     * @param string $type
     * @return $this
     */
    protected function setCurrentType($type)
    {
        if ($this->currentType == Descriptor::TYPE_DATA || $this->currentType == Descriptor::TYPE_METADATA) {
            $this->skipFirstLine = false;
            $this->inSeriesMatrix = false;
        }
        parent::setCurrentType($type);
        return $this;
    }

    /**
     * Parses metadata for a sample
     *
     * @param resource $fp
     * @return array
     */
    private function parseMetadata($fp)
    {
        $metadata = [
            'sample'   => [
                'name' => null,
            ],
            'metadata' => [],
        ];
        $inSample = false;
        $currentSample = null;
        $matches = null;
        while (($row = fgets($fp)) !== false) {
            $row = trim($row);
            if (preg_match(self::METADATA_SAMPLE_BEGIN, $row, $matches)) {
                $currentSample = $metadata['sample']['name'] = $matches[1];
                $inSample = true;
            } elseif ($inSample && preg_match(self::METADATA_SAMPLE_END, $row)) {
                break;
            } elseif ($inSample) {
                $matches = null;
                preg_match(self::METADATA_SAMPLE_ROW, $row, $matches);
                $this->metadataIndex[$matches[1]] = ['name' => ucwords(str_replace('_', ' ', $matches[1]))];
                $metadata['metadata'][] = [
                    'name'       => $this->metadataIndex[$matches[1]]['name'],
                    'value'      => $matches[2],
                    'sampleName' => $currentSample,
                ];
            }
        }
        return $metadata;
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
            if ($this->currentType == Descriptor::TYPE_METADATA) {
                return $this->parseMetadata($fp);
            }
            if ($this->currentType == Descriptor::TYPE_DATA && !$this->inSeriesMatrix) { //Find the beginning of the series matrix
                while (($row = fgets($fp)) !== false) {
                    $row = trim($row);
                    if (preg_match(self::SERIES_MATRIX_BEGIN, $row)) {
                        $this->inSeriesMatrix = true;
                    }
                }
            }
            $row = fgets($fp);
            if ($row !== false) {
                $row = trim($row);
                if ($this->currentType == Descriptor::TYPE_DATA && $this->inSeriesMatrix
                    && preg_match(self::SERIES_MATRIX_END, $row)
                ) { //Stops DATA parsing when series matrix ends
                    return null;
                }
                $this->currentIndex++;
                return $this->parser($row);
            }
        }
        return null;
    }

    /**
     * Get the current file pointer
     *
     * @return null|resource
     */
    protected function getFilePointer()
    {
        if ($this->currentType == Descriptor::TYPE_DATA
            && ($this->currentFilePointer === null
                || !is_resource($this->currentFilePointer)
                || feof($this->currentFilePointer)
                || $this->skipToNextFile
            )
        ) {
            $this->sampleIndex = null;
        }
        return parent::getFilePointer();
    }

    /**
     * Parse the metadata index and stores their positions
     *
     * @return array
     */
    protected function metadataIndexParser()
    {
        $this->skipToNextFile = true;
        return $this->metadataIndex;
    }

    /**
     * Parse a sample row
     *
     * @param array $row
     * @return array
     */
    protected function dataParser(array $row)
    {
        if ($this->sampleIndex === null) {
            array_shift($row);
            if (!count($row)) {
                throw new DataParserException('No sample identified in the current file (' . $this->currentFile . ').');
            }
            $this->sampleIndex = [];
            for ($i = 0; $i < count($row); $i++) {
                $this->sampleIndex[$i] = ++$this->lastSample;
            }
            return [];
        }
        $probe = array_shift($row);
        if (!count($row)) {
            throw new DataParserException('No sample identified in the current file (' . $this->currentFile . ').');
        }
        $data = [];
        for ($i = 0; $i < count($row); $i++) {
            $data[$this->sampleIndex[$i]] = doubleval($row[$i]);
        }
        return [
            'name' => $probe,
            'data' => $data,
        ];
    }

    /**
     * The real parser implementation
     *
     * @param string $row
     * @return mixed
     * @throws \App\Dataset\Parser\Exception\DataParserException
     */
    protected function parser($row)
    {
        if ($this->currentType == Descriptor::TYPE_DATA) {
            //@todo
        }
        throw new DataParserException('Unsupported data type "' . $this->currentType . '".');
    }

    /**
     * Returns the index of all metadata
     *
     * @return array|null
     */
    public function getMetadataIndex()
    {
        return $this->metadataIndex;
    }
}