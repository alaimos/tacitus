<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Factory;

use App\Dataset\Contracts\DescriptorAwareInterface;
use App\Dataset\Contracts\JobDataAwareInterface;
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
     * @param array $options
     *
     * @return \App\Models\Dataset
     */
    public function getDataset(array $options = []);

    /**
     * Create a new Probe model
     *
     * @param string $name
     * @param array  $data
     * @param array  $options
     *
     * @return \App\Models\Probe
     */
    public function getProbe($name, $data, array $options = []);

    /**
     * Create a new Metadata Model
     *
     * @param string             $name
     * @param string             $value
     * @param \App\Models\Sample $sample
     * @param array              $options
     *
     * @return \App\Models\Metadata
     */
    public function getMetadata($name, $value, Sample $sample, array $options = []);

    /**
     * Create a new Metadata Index Model
     *
     * @param string $name
     * @param array  $options
     *
     * @return \App\Models\MetadataIndex
     */
    public function getMetadataIndex($name, array $options = []);

    /**
     * Create a new Sample Model
     *
     * @param string $name
     * @param array  $options
     *
     * @return Sample
     */
    public function getSample($name, array $options = []);

}