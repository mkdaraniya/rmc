<?php

namespace Swissup\Geoip\Model\Provider;

use Swissup\Geoip\Api\Data\ProviderInterface;
use Swissup\Geoip\Model\Record;
use Swissup\Geoip\Model\RecordFactory;
use Swissup\Geoip\Model\ProviderContext;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractProvider implements ProviderInterface
{
    /**
     * @var RecordFactory
     */
    protected $recordFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var mixed
     */
    protected $rawRecord;

    /**
     * @param ProviderContext $context
     */
    public function __construct(ProviderContext $context)
    {
        $this->recordFactory = $context->getRecordFactory();
        $this->scopeConfig = $context->getScopeConfig();
        $this->logger = $context->getLogger();
        $this->cache = $context->getCache();
    }

    /**
     * Get specific config value
     *
     * @param  string $path
     * @param  string $scope
     * @return string
     */
    protected function getConfigValue($path, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue($path, $scope);
    }

    /**
     * @param  string $ip
     * @return Record
     */
    public function resolve($ip)
    {
        // $ip = '128.101.101.101'; // US
        // $ip = '54.195.241.132';  // IE

        if (!$this->rawRecord = $this->loadCache($ip)) {
            try {
                $this->rawRecord = $this->detect($ip);
                $this->saveCache($this->rawRecord, $ip);
            } catch (\Throwable $e) {
                $this->logException($e);
            }
        }

        $record = $this->recordFactory->create();

        if ($this->rawRecord) {
            $record->update([
                'rawRecord'   => $this->rawRecord,
                'cityName'    => $this->getCityName(),
                'regionCode'  => $this->getRegionCode(),
                'countryCode' => $this->getCountryCode(),
                'postalCode'  => $this->getPostalCode(),
            ]);
        }

        return $record;
    }

    /**
     * @param  \Throwable $e
     * @return void
     */
    protected function logException(\Throwable $e)
    {
        $this->logger->error('Swissup_Geoip: ' . $e->getMessage());
    }

    /**
     * @param  string $ip
     * @return mixed
     */
    protected function loadCache($ip)
    {
        if (!$this->isCacheable()) {
            return false;
        }

        if (!$cached = $this->cache->load($this->getCacheKey($ip))) {
            return false;
        }

        return $this->unserialize($cached);
    }

    /**
     * @param  mixed $data
     * @param  string $ip
     * @return void
     */
    protected function saveCache($data, $ip)
    {
        if (!$this->isCacheable() || !$data) {
            return false;
        }

        if ($cached = $this->serialize($data)) {
            $this->cache->save(
                $cached,
                $this->getCacheKey($ip),
                [],
                60 * 10
            );
        }
    }

    /**
     * @param  mixed $data
     * @return string
     */
    protected function serialize($data)
    {
        return json_encode($data);
    }

    /**
     * @param  string $data
     * @return array
     */
    protected function unserialize($data)
    {
        return json_decode($data, true);
    }

    /**
     * @param  string $ip
     * @return string
     */
    private function getCacheKey($ip)
    {
        return 'swissup_geoip_' . $ip;
    }
}
