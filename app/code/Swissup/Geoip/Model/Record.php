<?php

namespace Swissup\Geoip\Model;

class Record
{
    /**
     * @var string
     */
    private $cityName;

    /**
     * @var string
     */
    private $regionCode;

    /**
     * @var string
     */
    private $countryCode;

    /**
     * @var string
     */
    private $postalCode;

    /**
     * @var mixed
     */
    private $rawRecord;

    /**
     * @var \Magento\Directory\Model\Country
     */
    private $country;

    /**
     * @var \Magento\Directory\Model\Region
     */
    private $region;

    /**
     * @var \Magento\Customer\Api\Data\RegionInterface
     */
    private $customerRegion;

    /**
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Customer\Api\Data\RegionInterfaceFactory $customerRegionFactory
     */
    public function __construct(
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Customer\Api\Data\RegionInterfaceFactory $customerRegionFactory
    ) {
        $this->countryFactory = $countryFactory;
        $this->regionFactory = $regionFactory;
        $this->customerRegionFactory = $customerRegionFactory;
    }

    /**
     * @param  array $data
     * @return $this
     */
    public function update(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }

        return $this;
    }

    /**
     * @return boolean
     */
    public function isValid()
    {
        return (bool) $this->rawRecord;
    }

    /**
     * @return string
     */
    public function getCityName()
    {
        return $this->cityName;
    }

    /**
     * @return string
     */
    public function getRegionCode()
    {
        return $this->regionCode;
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @return mixed
     */
    public function getRawRecord()
    {
        return $this->rawRecord;
    }

    /**
     * @return \Magento\Directory\Model\Region
     */
    public function getRegion()
    {
        if (null === $this->region) {
            $this->region = $this->regionFactory->create()
                ->loadByCode(
                    $this->getRegionCode(),
                    $this->getCountry()->getId()
                );
        }
        return $this->region;
    }

    /**
     * @return \Magento\Customer\Api\Data\RegionInterface
     */
    public function getCustomerRegion()
    {
        if (null === $this->customerRegion) {
            $this->customerRegion = $this->customerRegionFactory->create()
                ->setRegionCode($this->getRegionCode())
                ->setRegionId($this->getRegion()->getId());
        }
        return $this->customerRegion;
    }

    /**
     * @return \Magento\Directory\Model\Country
     */
    public function getCountry()
    {
        if (null === $this->country) {
            $this->country = $this->countryFactory->create()
                ->loadByCode($this->getCountryCode());
        }
        return $this->country;
    }

    /**
     * @return array
     */
    public function toAddressArray()
    {
        return [
            'city'       => $this->getCityName(),
            'region'     => $this->getCustomerRegion(),
            'region_id'  => $this->getRegion()->getId(),
            'postcode'   => $this->getPostalCode(),
            'country_id' => $this->getCountryCode(),
        ];
    }
}
