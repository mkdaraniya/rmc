<?php

namespace Swissup\Taxvat\Plugin\Action;

use Magento\Framework\Exception\ValidatorException;

class MultishippingCheckItems
{
    /**
     * @var \Swissup\Taxvat\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Customer\Helper\Address
     */
    private $customerAddressHelper;

    /**
     * @var \Magento\Customer\Model\Address\Mapper
     */
    private $addressMapper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    private $response;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $json;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param \Swissup\Taxvat\Helper\Data $helper
     * @param \Magento\Customer\Helper\Address $customerAddressHelper
     * @param \Magento\Customer\Model\Address\Mapper $addressMapper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Swissup\Taxvat\Helper\Data $helper,
        \Magento\Customer\Helper\Address $customerAddressHelper,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->helper = $helper;
        $this->customerAddressHelper = $customerAddressHelper;
        $this->addressMapper = $addressMapper;
        $this->request = $request;
        $this->response = $response;
        $this->json = $json;
        $this->addressRepository = $addressRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function aroundExecute(
        \Magento\Multishipping\Controller\Checkout\CheckItems $subject,
        callable $proceed
    ) {
        $newAddress = $this->request->getPost('new_address');
        $continue = $this->request->getPost('continue');
        $info = $this->request->getPost('ship');

        if ($newAddress || !$continue || !is_array($info)) {
            return $proceed();
        }

        if (!$this->helper->canValidateVat()) {
            return $proceed();
        }

        $addressIds = [];
        foreach ($info as $itemData) {
            foreach ($itemData as $quoteItemId => $data) {
                if (empty($data['address'])) {
                    continue;
                }
                $addressIds[$data['address']] = $data['address'];
            }
        }

        $criteria = $this->searchCriteriaBuilder
            ->addFilter('entity_id', implode(',', $addressIds), 'in')
            ->create();
        $addresses = $this->addressRepository->getList($criteria);

        try {
            $success = true;
            $this->validateVat($addresses->getItems());
        } catch (ValidatorException $e) {
            $success = false;
            $this->response->representJson(
                $this->json->serialize([
                    'success' => false,
                    'error_message' => $e->getMessage(),
                ])
            );
        }

        if ($success) {
            return $proceed();
        }
    }

    private function validateVat($addresses)
    {
        foreach ($addresses as $address) {
            if ($this->helper->validateAddress($address)) {
                continue;
            }

            $renderer = $this->customerAddressHelper->getFormatTypeRenderer('oneline');
            $result = $renderer->renderArray($this->addressMapper->toFlatArray($address));

            throw new ValidatorException(__(
                'Please enter a valid VAT number in the following address: %1',
                $result
            ));
        }
    }
}
