<?php

namespace Swissup\Geoip\Api\Data;

use Swissup\Geoip\Model\Record;

interface ProviderInterface
{
    /**
     * @param  string $ip
     * @return mixed|void Must return raw record if detected
     */
    public function detect($ip);

    /**
     * @return string
     */
    public function getCityName();

    /**
     * @return string
     */
    public function getRegionCode();

    /**
     * @return string
     */
    public function getCountryCode();

    /**
     * @return string
     */
    public function getPostalCode();

    /**
     * @return boolean
     */
    public function isCacheable();
}
