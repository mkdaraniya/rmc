<?php

namespace Swissup\Geoip\Model\Config\Source;

use Swissup\Geoip\Model\ProviderFactory;

class Provider implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var ProviderFactory
     */
    protected $providerFactory;

    /**
     * @param ProviderFactory $providerFactory
     */
    public function __construct(ProviderFactory $providerFactory)
    {
        $this->providerFactory = $providerFactory;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];

        foreach ($this->providerFactory->getTypes() as $key => $values) {
            $result[] = [
                'value' => $key,
                'label' => __($values['label']),
            ];
        }

        return $result;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $result = [];
        foreach ($this->toOptionArray() as $item) {
            $result[$item['value']] = $item['label'];
        }
        return $result;
    }
}
