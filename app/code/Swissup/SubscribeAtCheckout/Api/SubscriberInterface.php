<?php

namespace Swissup\SubscribeAtCheckout\Api;

interface SubscriberInterface
{
    /**
     * Subscribe given email to store newsletter
     *
     * @param  $email
     * @return mixed (False in case of error)
     */
    public function subscribe($email);
}
