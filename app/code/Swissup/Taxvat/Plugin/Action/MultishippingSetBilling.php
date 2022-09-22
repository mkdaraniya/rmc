<?php

namespace Swissup\Taxvat\Plugin\Action;

class MultishippingSetBilling
{
    /**
     * @var \Swissup\Taxvat\Helper\Data
     */
    private $helper;

    /**
     * \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * \Magento\Framework\App\ResponseInterface
     */
    private $response;

    /**
     * \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * \Magento\Framework\App\Response\RedirectInterface
     */
    private $redirect;

    /**
     * \Magento\Customer\Api\AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @param \Swissup\Taxvat\Helper\Data $helper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        \Swissup\Taxvat\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
    ) {
        $this->helper = $helper;
        $this->request = $request;
        $this->response = $response;
        $this->messageManager = $messageManager;
        $this->redirect = $redirect;
        $this->addressRepository = $addressRepository;
    }

    public function aroundExecute(
        \Magento\Multishipping\Controller\Checkout\Address\SetBilling $subject,
        callable $proceed
    ) {
        $addressId = $this->request->getParam('id');

        if (!$addressId) {
            return $proceed();
        }

        if (!$this->helper->canValidateVat()) {
            return $proceed();
        }

        try {
            $address = $this->addressRepository->getById($addressId);
        } catch (\Exception $e) {
            return $proceed();
        }

        if ($this->helper->validateAddress($address)) {
            return $proceed();
        }

        $this->messageManager->addErrorMessage(__('Please enter a valid VAT number.'));

        $this->redirect->redirect(
            $this->response,
            $this->redirect->getRedirectUrl()
        );
    }
}
