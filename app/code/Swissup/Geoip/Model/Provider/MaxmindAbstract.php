<?php

namespace Swissup\Geoip\Model\Provider;

abstract class MaxmindAbstract extends AbstractProvider
{
    /**
     * @param  string $ip
     * @return mixed
     */
    public function detect($ip)
    {
        try {
            return $this->getReader()->city($ip);
        } catch (\GeoIp2\Exception\AddressNotFoundException $e) {
            // don't log exception when address is not detected
        }
    }

    /**
     * @return string
     */
    public function getCityName()
    {
        return $this->rawRecord->city->name;
    }

    /**
     * @return string
     */
    public function getRegionCode()
    {
        return $this->rawRecord->mostSpecificSubdivision->isoCode;
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->rawRecord->country->isoCode;
    }

    /**
     * @return string
     */
    public function getPostalCode()
    {
        return $this->rawRecord->postal->code;
    }

    /**
     * @param  string $data
     * @return mixed
     */
    protected function unserialize($data)
    {
        $data = parent::unserialize($data);

        if ($data) {
            return new \GeoIp2\Model\City($data);
        }

        return false;
    }
}
