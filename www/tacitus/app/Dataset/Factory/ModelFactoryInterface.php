<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Factory;

use App\Dataset\DescriptorAwareInterface;
use App\Dataset\JobDataAwareInterface;
use App\Models\Sample;

/**
 * Interface ModelFactoryInterface
 *
 * @package App\Dataset\Parser\Data
 */
interface ModelFactoryInterface extends DescriptorAwareInterface, JobDataAwareInterface
{

    /**
     * Get a Dataset object associated with the current descriptor.
     * If no Dataset object is available, it will be instantiated.
     *
     * @return \App\Models\Dataset
     */
    public function getDataset();

    /**
     * Create a new Data model
     *
     * @param string             $probe
     * @param string             $value
     * @param \App\Models\Sample $sample
     *
     * @return \App\Models\Data
     */
    public function getData($probe, $value, Sample $sample);

    /**
     * Create a new Metadata Model
     *
     * @param string             $name
     * @param string             $value
     * @param \App\Models\Sample $sample
     *
     * @return \App\Models\Metadata
     */
    public function getMetadata($name, $value, Sample $sample);

    /**
     * Create a new Metadata Index Model
     *
     * @param string $name
     *
     * @return \App\Models\MetadataIndex
     */
    public function getMetadataIndex($name);

    /**
     * Create a new Sample Model
     *
     * @param string $name
     *
     * @return \App\Models\Sample
     */
    public function getSample($name);

}