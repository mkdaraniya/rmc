<?php

namespace Swissup\Geoip\Model\Provider;

use GeoIp2\WebService\Client;

class MaxmindService extends MaxmindAbstract
{
    const ACCOUNT_ID_CONFIG = 'geoip/main/maxmind_service_account_id';
    const LICENSE_KEY_CONFIG = 'geoip/main/maxmind_service_license_key';

    /**
     * @return Client
     */
    protected function getReader()
    {
        return new Client(
            $this->getConfigValue(self::ACCOUNT_ID_CONFIG),
            $this->getConfigValue(self::LICENSE_KEY_CONFIG)
        );
    }

    /**
     * @return boolean
     */
    public function isCacheable()
    {
        return true;
    }
}
