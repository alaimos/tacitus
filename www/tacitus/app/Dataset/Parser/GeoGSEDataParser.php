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
     * Get the position of a sample
     *
     * @var array
     */
    protected $sampleToPosition = [];

    /**
     * A list of supported metadata
     *
     * @var array|null
     */
    protected $supportedMetadata = [];

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
     * Unesape a string
     *
     * @param string $string
     *
     * @return string
     */
    private function unescape($string)
    {
        return stripslashes(trim($string, '"\''));
    }

    /**
     * Unescape a metadata name
     *
     * @param string $string
     *
     * @return string
     */
    private function unescapeMetadataName($string)
    {
        return ucwords(str_replace('_', ' ', $this->unescape($string)));
    }

    /**
     * Internal method to set the current type in order to use a fluent interface
     *
     * @param string $type
     *
     * @return $this
     */
    protected function setCurrentType($type)
    {
        $this->skipFirstLine = false;
        $this->inSeriesMatrix = false;
        if ($type == Descriptor::TYPE_METADATA_INDEX) {
            $this->identifierIndex = null;
        }
        if ($type == Descriptor::TYPE_METADATA) {
            $this->lastSample = -1;
        }
        parent::setCurrentType($type);
        return $this;
    }

    /**
     * Checks and completes meta array
     *
     * @param array  $metadata
     * @param string $currentSample
     * @param array  $filled
     *
     * @return bool
     */
    private function checkMetaArray(array &$metadata, &$currentSample, array &$filled)
    {
        if ($currentSample === null) return false;
        if (!isset($this->sampleToPosition[$currentSample])) {
            $this->sampleToPosition[$currentSample] = ++$this->lastSample;
        }
        $metadata['sample']['position'] = $this->sampleToPosition[$currentSample];
        foreach ($this->supportedMetadata as $key => $meta) {
            if (!isset($filled[$key])) {
                $metadata['metadata'][] = [
                    'name'       => $meta,
                    'value'      => '',
                    'sampleName' => $currentSample,
                ];
                $filled[$key] = true;
            }
        }
        return true;
    }

    /**
     * Parses metadata for a sample
     *
     * @param resource $fp
     *
     * @return array|boolean
     */
    private function metadataParser($fp)
    {
        $allMeta = [];
        $metadata = [
            'sample'   => [
                'name'     => null,
                'platform' => null,
                'position' => null,
            ],
            'metadata' => [],
        ];
        $inSample = false;
        $multiSample = false;
        $currentSample = null;
        $matches = null;
        $filled = [];
        while (($row = fgets($fp)) !== false) {
            $row = trim($row);
            $matches = null;
            if (!$inSample && preg_match(self::METADATA_SAMPLE_BEGIN, $row, $matches)) {
                $currentSample = $metadata['sample']['name'] = $this->unescape($matches[1]);
                $inSample = true;
            } elseif ($inSample && preg_match(self::METADATA_SAMPLE_END, $row)) {
                break;
            } elseif ($inSample && preg_match(self::METADATA_SAMPLE_ROW, $row, $matches)) {
                if ($matches[1] == self::PLATFORM_ID) {
                    $metadata['sample']['platform'] = $matches[2];
                    continue;
                }
                $filled[$matches[1]] = true;
                $metadata['metadata'][] = [
                    'name'       => $this->supportedMetadata[$matches[1]],
                    'value'      => $this->unescape($matches[2]),
                    'sampleName' => $currentSample,
                ];
            } elseif ($inSample && preg_match(self::METADATA_SAMPLE_BEGIN, $row, $matches)) {
                $multiSample = true;
                if ($this->checkMetaArray($metadata, $currentSample, $filled)) {
                    $allMeta[] = $metadata;
                }
                $metadata = [
                    'sample'   => [
                        'name'     => $this->unescape($matches[1]),
                        'platform' => null,
                        'position' => null,
                    ],
                    'metadata' => [],
                ];
                $filled = [];
                $currentSample = $metadata['sample']['name'];
            }
            $this->currentIndex++;
        }
        if (!$this->checkMetaArray($metadata, $currentSample, $filled)) {
            if (!$multiSample) return false;
        } else {
            if ($multiSample) {
                $allMeta[] = $metadata;
            }
        }
        return ($multiSample) ? $allMeta : $metadata;
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
                return $this->metadataParser($fp);
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
     * Parses metadata index file
     *
     * @param string $row
     *
     * @return array|bool
     */
    private function metadataIndexParser($row)
    {
        if (preg_match(self::METADATA_SAMPLE_ROW, $row, $matches)) {
            $idx = $matches[1];
            if (!isset($this->supportedMetadata[$idx])) {
                $name = $this->unescapeMetadataName($matches[1]);
                $this->supportedMetadata[$idx] = $name;
                return [
                    'name' => $name,
                ];
            }
        }
        return false;
    }

    /**
     * Parse a sample row
     *
     * @param string $row
     *
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
                    throw new DataParserException('No sample identified in the current file (' . $this->currentFile
                                                  . ').');
                }
                $this->sampleIndex = [];
                for ($i = 0; $i < count($row); $i++) {
                    $row[$i] = $this->unescape($row[$i]);
                    $this->sampleIndex[$i] = (isset($this->sampleToPosition[$row[$i]])) ? $this->sampleToPosition[$row[$i]] : null;
                }
                return false;
            }
            $probe = array_shift($row);
            if (!count($row)) {
                throw new DataParserException('No sample identified in the current file (' . $this->currentFile . ').');
            }
            $probe = $this->unescape($probe);
            $data = [];
            for ($i = 0; $i < count($row); $i++) {
                if ($this->sampleIndex[$i] !== null) {
                    $data[$this->sampleIndex[$i]] = doubleval($row[$i]);
                }
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
     *
     * @return mixed
     * @throws \App\Dataset\Parser\Exception\DataParserException
     */
    protected function parser($row)
    {
        if ($this->currentType == Descriptor::TYPE_DATA) {
            return $this->dataParser($row);
        } elseif ($this->currentType == Descriptor::TYPE_METADATA_INDEX) {
            return $this->metadataIndexParser($row);
        }
        throw new DataParserException('Unsupported data type "' . $this->currentType . '".');
    }

}