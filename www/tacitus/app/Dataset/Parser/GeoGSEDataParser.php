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
    const METADATA_SAMPLE_BEGIN = '/^\\^sample\s+=\s+(.*)/i';

    /**
     * Regular expression to check for metadata sample end
     */
    const METADATA_SAMPLE_END = '/^!sample_table_end/i';

    /**
     * Regular expression to extract data from a metadata sample row
     */
    const METADATA_SAMPLE_ROW = '/!sample_(\S+)\s+=\s+(.*)/i';

    /**
     * Regular expression to check for series table begin
     */
    const SERIES_MATRIX_BEGIN = '/^!series_matrix_table_begin/i';

    /**
     * Regular expression to check for series matrix end
     */
    const SERIES_MATRIX_END = '/^!series_matrix_table_end/i';

    /**
     * Names of sample metadata which will contain platform_id
     */
    const PLATFORM_ID = 'platform_id';

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
        $this->skipFirstLine = false;
        $this->inSeriesMatrix = false;
        if ($type == Descriptor::TYPE_METADATA_INDEX) {
            $this->identifierIndex = null;
        }
        if ($type == Descriptor::TYPE_DATA) {
            $this->lastSample = -1;
        }
        parent::setCurrentType($type);
        return $this;
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
        $this->reset()->setCurrentType($type)->initFilesList();
        if ($type == Descriptor::TYPE_METADATA_INDEX) {
            $this->totalCount = 1;
            $this->currentIndex = 0;
            return $this;
        }
        return $this->checkFiles()->initCounter();
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
                'name'     => null,
                'platform' => null,
            ],
            'metadata' => [],
        ];
        $inSample = false;
        $currentSample = null;
        $matches = null;
        while (($row = fgets($fp)) !== false) {
            $row = trim($row);
            $matches = null;
            if (preg_match(self::METADATA_SAMPLE_BEGIN, $row, $matches)) {
                $currentSample = $metadata['sample']['name'] = $matches[1];
                $inSample = true;
            } elseif ($inSample && preg_match(self::METADATA_SAMPLE_END, $row)) {
                break;
            } elseif ($inSample && preg_match(self::METADATA_SAMPLE_ROW, $row, $matches)) {
                if ($matches[1] == self::PLATFORM_ID) {
                    $metadata['sample']['platform'] = $matches[2];
                    continue;
                }
                $this->metadataIndex[$matches[1]] = ['name' => ucwords(str_replace('_', ' ', $matches[1]))];
                $metadata['metadata'][] = [
                    'name'       => $this->metadataIndex[$matches[1]]['name'],
                    'value'      => $matches[2],
                    'sampleName' => $currentSample,
                ];
            }
            $this->currentIndex++;
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
        if ($this->currentType == Descriptor::TYPE_METADATA_INDEX) {
            if ($this->currentIndex == 0) {
                $this->currentIndex++;
                return array_values($this->metadataIndex);
            } else {
                return null;
            }
        } else {
            $fp = $this->getFilePointer();
            if ($fp) {
                if ($this->currentType == Descriptor::TYPE_METADATA) {
                    return $this->parseMetadata($fp);
                } else {
                    $row = fgets($fp);
                    if ($row !== false) {
                        $row = trim($row);
                        $this->currentIndex++;
                        return $this->parser($row);
                    }
                }
            }
            return null;
        }
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
     * Parse a sample row
     *
     * @param string $row
     * @return array|boolean
     */
    protected function dataParser($row)
    {
        if (!$this->inSeriesMatrix) {
            if (preg_match(self::SERIES_MATRIX_BEGIN, $row)) {
                $this->inSeriesMatrix = true;
            }
            return false;
        } else {
            if (preg_match(self::SERIES_MATRIX_END, $row)) {
                $this->inSeriesMatrix = false;
                return false;
            }
            $row = explode("\t", $row);
            if ($this->sampleIndex === null) {
                array_shift($row);
                if (!count($row)) {
                    throw new DataParserException('No sample identified in the current file (' . $this->currentFile . ').');
                }
                $this->sampleIndex = [];
                for ($i = 0; $i < count($row); $i++) {
                    $this->sampleIndex[$i] = ++$this->lastSample;
                }
                return false;
            }
            $probe = array_shift($row);
            if (!count($row)) {
                throw new DataParserException('No sample identified in the current file (' . $this->currentFile . ').');
            }
            $probe = stripslashes(trim($probe, '"\''));
            $data = [];
            for ($i = 0; $i < count($row); $i++) {
                $data[$this->sampleIndex[$i]] = doubleval($row[$i]);
            }
            return [
                'name' => $probe,
                'data' => $data,
            ];
        }
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
            return $this->dataParser($row);
        }
        throw new DataParserException('Unsupported data type "' . $this->currentType . '".');
    }

}