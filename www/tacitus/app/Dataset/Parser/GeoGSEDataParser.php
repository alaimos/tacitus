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
     * Index used to find metadata names from position
     *
     * @var array|null
     */
    protected $supportedMetadataIndex = null;

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

    protected $inSeriesMatrix = false;

    protected $currentSample = null;

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
                $metadata = [
                    'sample'   => [
                        'name' => null,
                    ],
                    'metadata' => [],
                ];
                $inSample = false;
                $matches = null;
                while (($row = fgets($fp)) !== false) {
                    $row = trim($row);
                    if (preg_match(self::METADATA_SAMPLE_BEGIN, $row, $matches)) {
                        $metadata['sample']['name'] = $matches[1];
                        $this->currentSample = $metadata['sample']['name'];
                        $inSample = true;
                    } elseif ($inSample && preg_match(self::METADATA_SAMPLE_END, $row)) {
                        $this->currentSample = null;
                        break;
                    } elseif ($inSample) {
                        $metadata['metadata'][] = $this->parser($row);
                    }
                }
                return $metadata;
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
     * @param array $row
     * @return array
     */
    protected function metadataIndexParser(array $row)
    {
        if (!is_array($this->supportedMetadataIndex)) {
            $this->supportedMetadataIndex = [];
        }
        $this->skipToNextFile = true;
        if (!count($row)) {
            throw new DataParserException("No metadata fields have been found.");
        }
        $results = [];
        $supportedFields = 0;
        for ($i = 0; $i < count($row); $i++) {
            $item = $row[$i];
            $matches = null;
            if (preg_match(self::IDENTIFIERS_REGEXP, $item, $matches)) {
                if ($this->identifierIndex == null) {
                    $this->identifierIndex = $i;
                } else {
                    $this->supportedMetadataIndex[$i] = ucwords($matches[1]);
                    $results[] = ['name' => $this->supportedMetadataIndex[$i]];
                }
                $supportedFields++;
            } elseif (preg_match(self::METADATA_REGEXP, $item, $matches)) {
                $this->supportedMetadataIndex[$i] = ucwords((isset($matches[2])) ? $matches[2] : $matches[1]);
                $results[] = ['name' => $this->supportedMetadataIndex[$i]];
                $supportedFields++;
            }
        }
        if (!$supportedFields) {
            throw new DataParserException("No supported metadata fields have been found.");
        }
        return $results;
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
        if ($this->currentType == Descriptor::TYPE_METADATA) {
            $matches = null;
            preg_match(self::METADATA_SAMPLE_ROW, $row, $matches);
            return [
                'name'       => ucwords(str_replace('_', ' ', $matches[1])),
                'value'      => $matches[2],
                'sampleName' => $this->currentSample,
            ];
        } else {
            //@todo
        }
        /*$row = explode("\t", $row);
        switch ($this->currentType) {
            case Descriptor::TYPE_METADATA_INDEX:
                return $this->metadataIndexParser($row);
            case Descriptor::TYPE_METADATA:
                return $this->metadataParser($row);
            case Descriptor::TYPE_DATA:
                return $this->dataParser($row);
            default:
                throw new DataParserException('Unsupported data type "' . $this->currentType . '".');
        }*/
    }
}