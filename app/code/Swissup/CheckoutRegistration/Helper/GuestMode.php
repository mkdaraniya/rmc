<?php

namespace Swissup\CheckoutRegistration\Helper;

use Swissup\CheckoutRegistration\Model\System\Config\Source\RegistrationMode;

class GuestMode extends Data
{
    public function isComponentDisabled()
    {
        return $this->getRegistrationMode() !== RegistrationMode::GUEST_ONLY;
    }
}
