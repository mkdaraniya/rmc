<?php

namespace Swissup\Geoip\Model;

use Swissup\Geoip\Model\RecordFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\CacheInterface;
use Psr\Log\LoggerInterface;

class ProviderContext
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
     * @param RecordFactory $recordFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     * @param CacheInterface $cache
     */
    public function __construct(
        RecordFactory $recordFactory,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        CacheInterface $cache
    ) {
        $this->recordFactory = $recordFactory;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->cache = $cache;
    }

    /**
     * @return RecordFactory
     */
    public function getRecordFactory()
    {
        return $this->recordFactory;
    }

    /**
     * @return ScopeConfigInterface
     */
    public function getScopeConfig()
    {
        return $this->scopeConfig;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return CacheInterface
     */
    public function getCache()
    {
        return $this->cache;
    }
}
