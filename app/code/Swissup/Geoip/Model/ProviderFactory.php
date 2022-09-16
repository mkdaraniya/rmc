<?php

namespace Swissup\Geoip\Model;

class ProviderFactory
{
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $types
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $types = []
    ) {
        $this->objectManager = $objectManager;
        $this->types = $types;
    }

    /**
     * @param string $type
     * @return \Swissup\Geoip\Api\Data\ProviderInterface
     */
    public function create($type)
    {
        if (!isset($this->types[$type])) {
            throw new \InvalidArgumentException("{$type} is not a valid type");
        }
        return $this->objectManager->create($this->types[$type]['class']);
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }
}
