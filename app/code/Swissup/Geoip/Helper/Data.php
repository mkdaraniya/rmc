<?php

namespace Swissup\Geoip\Helper;

use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Swissup\Geoip\Model\ProviderFactory;

class Data
{
    const ENABLE_CONFIG = 'geoip/main/enable';
    const PROVIDER_CONFIG = 'geoip/main/provider';

    /**
     * @var RemoteAddress
     */
    protected $remoteAddress;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ProviderFactory
     */
    protected $providerFactory;

    /**
     * @var array
     */
    protected $memo = [];

    /**
     * @param RemoteAddress $remoteAddress
     * @param ScopeConfigInterface $scopeConfig
     * @param ProviderFactory $providerFactory
     */
    public function __construct(
        RemoteAddress $remoteAddress,
        ScopeConfigInterface $scopeConfig,
        ProviderFactory $providerFactory
    ) {
        $this->remoteAddress = $remoteAddress;
        $this->scopeConfig = $scopeConfig;
        $this->providerFactory = $providerFactory;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return (bool) $this->scopeConfig->getValue(
            self::ENABLE_CONFIG,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param  string $ip
     * @return \Swissup\Geoip\Model\Record
     */
    public function detect($ip = null)
    {
        if (!$ip) {
            $ip = $this->remoteAddress->getRemoteAddress();
        }

        if (!isset($this->memo[$ip])) {
            $this->memo[$ip] = $this->getProvider()->resolve($ip);
        }

        return $this->memo[$ip];
    }

    protected function getProvider()
    {
        $type = $this->scopeConfig->getValue(
            self::PROVIDER_CONFIG,
            ScopeInterface::SCOPE_STORE
        );
        return $this->providerFactory->create($type);
    }
}
