<?php

namespace Swissup\Geoip\Model\Provider;

use Swissup\Geoip\Model\ProviderContext;
use Magento\Framework\HTTP\ClientFactory;

class Ipstack extends AbstractProvider
{
    const BASE_URL = 'http://api.ipstack.com/';

    const API_KEY_CONFIG = 'geoip/main/ipstack_api_key';

    /**
     * @var ClientFactory
     */
    protected $httpClientFactory;

    /**
     * @param ProviderContext $context
     * @param ClientFactory $httpClientFactory
     */
    public function __construct(
        ProviderContext $context,
        ClientFactory $httpClientFactory
    ) {
        parent::__construct($context);

        $this->httpClientFactory = $httpClientFactory;
    }

    /**
     * @param  string $ip
     * @return mixed
     */
    public function detect($ip)
    {
        $client = $this->httpClientFactory->create();
        $client->get($this->getUri($ip));

        $data = json_decode($client->getBody(), true);

        if (!$data) {
            return;
        }

        if (!empty($data['error'])) {
            throw new \Exception($data['error']['info']);
        }

        if (!empty($data['country_code']) && !empty($data['region_code'])) {
            return $data;
        }
    }

    /**
     * @return string
     */
    public function getCityName()
    {
        return $this->rawRecord['city'];
    }

    /**
     * @return string
     */
    public function getRegionCode()
    {
        return $this->rawRecord['region_code'];
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->rawRecord['country_code'];
    }

    /**
     * @return string
     */
    public function getPostalCode()
    {
        return $this->rawRecord['zip'];
    }

    /**
     * @return boolean
     */
    public function isCacheable()
    {
        return true;
    }

    /**
     * @param  string $ip
     * @return string
     */
    private function getUri($ip)
    {
        return self::BASE_URL . $ip
            . '?access_key=' . $this->getConfigValue(self::API_KEY_CONFIG)
            . '&output=json';
    }
}
