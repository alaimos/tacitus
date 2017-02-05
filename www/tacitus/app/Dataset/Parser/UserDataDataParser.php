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
 * Class UserDataDataParser
 *
 * @package App\Dataset\Parser
 */
class UserDataDataParser extends AbstractDataParser
{

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

    /**
     * Format metadata header
     *
     * @param string $text
     *
     * @return string
     */
    private function formatMeta($text)
    {
        return preg_replace('/\s+/', ' ', preg_replace('/[^a-zA-Z0-9]/', ' ', ucwords($text)));
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
        if ($type == Descriptor::TYPE_METADATA) {
            $this->skipFirstLine = true;
        } else {
            $this->skipFirstLine = false;
        }
        if ($type == Descriptor::TYPE_METADATA_INDEX) {
            $this->identifierIndex = null;
            $this->supportedMetadataIndex = null;
        }
        if ($type == Descriptor::TYPE_DATA) {
            $this->lastSample = -1;
        }
        parent::setCurrentType($type);
        return $this;
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
     * Parse a metadata row
     *
     * @param array $row
     *
     * @return array|boolean
     */
    protected function metadataParser(array $row)
    {
        if ($this->identifierIndex === null || $this->supportedMetadataIndex === null) {
            throw new DataParserException("Metadata Index must be parsed first.");
        }
        if (!count($row)) {
            return false;
        }
        $result = [
            'sample'   => [
                'name' => $row[$this->identifierIndex],
            ],
            'metadata' => [],
        ];
        if (empty($result['sample']['name'])) {
            return false;
        }
        foreach ($this->supportedMetadataIndex as $index => $name) {
            $result['metadata'][] = [
                'name'       => $name,
                'value'      => (isset($row[$index])) ? $row[$index] : '',
                'sampleName' => $result['sample']['name'],
            ];
        }
        return $result;
    }

    /**
     * Parse the metadata index and stores their positions
     *
     * @param array $row
     *
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
        $fileMeta = $this->getDescriptor()->getFilesMetadata(Descriptor::TYPE_METADATA_INDEX, $this->currentFile);
        $hasSampleIdHeader = (isset($fileMeta['hasSampleIdHeader'])) ? $fileMeta['hasSampleIdHeader'] : true;
        $results = [];
        $this->identifierIndex = 0;
        $supportedFields = 0;
        $init = ($hasSampleIdHeader) ? 1 : 0;
        $shift = ($hasSampleIdHeader) ? 0 : 1;
        for ($i = $init; $i < count($row); $i++) {
            $this->supportedMetadataIndex[$shift + $i] = $this->formatMeta($row[$i]);
            $results[] = ['name' => $this->supportedMetadataIndex[$shift + $i]];
            $supportedFields++;
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
     *
     * @return array|boolean
     */
    protected function dataParser(array $row)
    {
        if ($this->sampleIndex === null) {
            $fileMeta = $this->getDescriptor()->getFilesMetadata(Descriptor::TYPE_DATA, $this->currentFile);
            $hasSampleIdHeader = (isset($fileMeta['hasSampleIdHeader'])) ? $fileMeta['hasSampleIdHeader'] : true;
            if ($hasSampleIdHeader) array_shift($row);
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
     *
     * @return mixed
     * @throws \App\Dataset\Parser\Exception\DataParserException
     */
    protected function parser($row)
    {
        $row = explode("\t", $row);
        switch ($this->currentType) {
            case Descriptor::TYPE_METADATA_INDEX:
                return $this->metadataIndexParser($row);
            case Descriptor::TYPE_METADATA:
                return $this->metadataParser($row);
            case Descriptor::TYPE_DATA:
                return $this->dataParser($row);
            default:
                throw new DataParserException('Unsupported data type "' . $this->currentType . '".');
        }
    }
}