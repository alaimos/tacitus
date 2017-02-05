<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Parser;

use App\Dataset\Contracts\DescriptorAwareInterface;

/**
 * Interface DataParserInterface
 *
 * @package App\Dataset\Parser
 */
interface DataParserInterface extends DescriptorAwareInterface
{

    /**
     * Initializes the parsing of all data files associated with a specific type
     *
     * @param string $type
     *
     * @return \App\Dataset\Parser\DataParserInterface
     * @throws \App\Dataset\Parser\Exception\DataParserException
     */
    public function start($type);

    /**
     * Parse one element. This function returns something until all the files have been parsed.
     * A null output occurs when nothing to parse remain.
     *
     * @return mixed|null
     * @throws \App\Dataset\Parser\Exception\DataParserException
     */
    public function parse();

    /**
     * The total number of elements to parse in the current type or null if no element is being parsed.
     *
     * @return integer|null
     */
    public function count();

    /**
     * The index of the current element being parsed or null if no element is being parsed.
     *
     * @return integer|null
     */
    public function current();

    /**
     * End parse and closes all pointers
     *
     * @return \App\Dataset\Parser\DataParserInterface
     */
    public function end();


}