<?php

namespace Scrumwheel\Shippy\Cron;

use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Magento\Cron\Model\Schedule;
use Magento\Framework\Serialize\SerializerInterface;

class DelayedShippyApi
{
    protected $logger;
    protected SerializerInterface $serializer;

    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer
    ) {
        $this->logger = $logger;
        $this->serializer = $serializer;
    }

    public function execute(Schedule $schedule)
    {
        $arguments = [];
        if ($schedule->getArguments()) {
            $arguments = $this->serializer->unserialize($schedule->getArguments());
        }

        // Your logic here to call the api
        $this->logger->info('Delayed shippy api cron job executed.');

        $this->logger->info(json_encode($arguments));

    }
}
