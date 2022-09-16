<?php

namespace Swissup\SubscribeAtCheckout\Model;

use Swissup\SubscribeAtCheckout\Api\SubscriberInterface;

class Subscriber implements SubscriberInterface
{
    protected $driverFactory;

    public function __construct(
        \Magento\Newsletter\Model\SubscriberFactory $driverFactory
    ) {
        $this->driverFactory = $driverFactory;
    }

    /**
     * @param  [type] $email [description]
     * @return [type]        [description]
     */
    public function subscribe($email)
    {
        try {
            return $this->driverFactory->create()->subscribe($email);
        } catch (\Exception $e) {
            return false;
        }
    }
}
