<?php

namespace BexioSync\BexioSync\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

class Order extends Action
{

    public function execute()
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // instance of object manager
        $scopeConfig = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');

        $request = $objectManager->get('\Magento\Framework\App\Request\Http');
        $resultFactory = $objectManager->get('Magento\Framework\Controller\ResultFactory');
        $resultRedirect = $resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $orderId = $request->getParam('order_id');

        $sectionId = 'config';
        $groupId = 'settings';
        $fieldId = 'enable';

        $configPath = $sectionId . '/' . $groupId . '/' . $fieldId;
        $value =  $scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$value) {
            return true;
        }

        // $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/bexiosync.log');
        // $logger = new \Zend_Log();
        // $logger->addWriter($writer);
        // $logger->info(__METHOD__);
        // $logger->info("Order Sync Action Start");

        try {
            $orderInterface = $objectManager->get('\Magento\Sales\Api\Data\OrderInterface');
            $order = $orderInterface->load($orderId);

            // create customer on bexio
            $curl2 = curl_init();
            $customerData = array(
                'salutation_type' => "male", //$order->getCustomerGender(),
                'firstname' => $order->getCustomerFirstname(),
                'lastname' => $order->getCustomerLastname(),
                'email' => $order->getCustomerEmail(),
                'title_id' => "",
            );

            curl_setopt_array($curl2, array(
                CURLOPT_URL => 'https://api.bexio.com/3.0/fictional_users',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($customerData),
                CURLOPT_HTTPHEADER => array(
                    'Accept: application/json',
                    'Authorization: Bearer eyJraWQiOiI2ZGM2YmJlOC1iMjZjLTExZTgtOGUwZC0wMjQyYWMxMTAwMDIiLCJhbGciOiJSUzI1NiJ9.eyJzdWIiOiJtYXJqYW5AYnVjaGVtLmNvbSIsImxvZ2luX2lkIjoiNmIyY2ZlM2UtNTVhYS00OTM0LThjY2ItNzgxMGU4ZjY3MTc4IiwiY29tcGFueV9pZCI6ImtmeWVqYnJxM3BsdiIsInVzZXJfaWQiOjMzNTkzNiwiYXpwIjoiZXZlcmxhc3QtdG9rZW4tb2ZmaWNlLWNsaWVudCIsInNjb3BlIjoib3BlbmlkIHByb2ZpbGUgZW1haWwgYWxsIHRlY2huaWNhbCIsImlzcyI6Imh0dHBzOlwvXC9pZHAuYmV4aW8uY29tIiwiZXhwIjozMjU3MzI1NDgwLCJpYXQiOjE2ODA1MjU0ODAsImNvbXBhbnlfdXNlcl9pZCI6MSwianRpIjoiZTE3ZWQ4YTMtZmRkYy00M2VkLTllMjItNjdiMDhjMWJjNjNjIn0.L4rNyP-ypCp890rWWc2cSCxcelf1FuY_rpvNI6YgiC0n69fgt_ilpEZmnZXuBSoVxzusWA9FuCk9MOWqjatFurU9BQy5kwt-sADDAgVWfoGFHiyZR1SFbq5P26zgofpwAA1zoVk7YudEEkl5SONTYpXXgQApmiSh5B7matjPGfBlk8qLhHxLFQYM3YPJaS-7Yp8kIvuOn6sSFs3WvuL-Wqfb96qQKxkB0oUEXrVy1aPxV7Xt4TC5edPGbD0CLX7SbGUZiNFWm59IQS9zeRIJPr5HufokhaeRXfull4xUy4uO-VxT535kAUxH_gBpjW8jhgC4TldkKMcIFsqoWKuI3sxL_8iLXCCF1dDytUUwrUoVvQs1NFV0WRpuE0R_IUQL6vlsFRbLvy-NLe0rV0r8Nei06cq-zcwNaGmnfUvUjSMTSwVpKtftxYXwhY8WipB4Jo4D_LSfUDx0kpOEAxFwwOB4J3EsO9TheQ9i8b3q2jhykrDUfKsiechH7uNiyvN-c2PL8NQd-rpDDZoJP9A3_ywZ2sOThDIEese6lrJtNibBY4i2ZIb7_FX5lg1du9KP3vgPLRgyYkoLe-zF5TLkZao75Y8KBU1YgteLRbE-VC4kUnTBrfmDukxqx8kvPP0ljp2on7HdpA7F1wj1sj_hvKlCcqxxZjwiBqsjjy41FUA',
                    'Content-Type: application/json'
                ),
            ));

            $response2 = curl_exec($curl2);
            $customerRes = json_decode($response2);

            $userId = '';
            $customerId = $order->getCustomerId();
            $customer = $objectManager->create('Magento\Customer\Model\Customer')->load($customerId);

            if ($customer->getData('external_customer_id') != '') {
                $userId = $customer->getData('external_customer_id');
            }else{
                if(isset($customerRes->error_code) && $customerRes->error_code != '' && $customerRes->error_code == 422){
                    $this->messageManager->addErrorMessage(__('User already exist.'));        
                    return $resultRedirect->setPath('sales/order/index');
                }
            }

            if (isset($customerRes->id) && !empty($customerRes->id)) {
                if ($customer->getData('external_customer_id') != '') {
                    $userId = $customer->getData('external_customer_id');
                } else {
                    // load customer by id
                    $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                    $connection = $resource->getConnection();
                    $tableName = $resource->getTableName('customer_entity'); //gives table name with 
                    //Update Data into table
                    $sql = "Update " . $tableName . " Set external_customer_id = " . $customerRes->id . " where email = '" . $customer->getEmail() . "'";
                    $connection->query($sql);

                    $userId = $customerRes->id;
                }
            } else {
                $userId = $customer->getData('external_customer_id');
            }
            // sync contact / address detail 
            $billingAddr = $order->getBillingAddress();
            $curl3 = curl_init();

            $addressData = [
                "contact_type_id" => 1,
                "name_1" => $billingAddr->getData('firstname'),
                "name_2" => $billingAddr->getData('lastname'),
                "salutation_id" =>  2,
                "salutation_form" => null,
                "titel_id" => null,
                "birthday" => null,
                "address" => $billingAddr->getData('street'),
                "postcode" => $billingAddr->getData('postcode'),
                "city" => $billingAddr->getData('city'),
                "country_id" => 1,
                "mail" => $order->getCustomerEmail(),
                "mail_second" => "",
                "phone_fixed" => "",
                "phone_fixed_second" => "",
                "phone_mobile" => $billingAddr->getData('telephone'),
                "fax" => "",
                "url" => "",
                "skype_name" => "",
                "remarks" => "",
                "language_id" => "1",
                "contact_group_ids" => "1,2",
                "contact_branch_ids" => null,
                "user_id" => $userId,
                "owner_id" => 1
            ];

            curl_setopt_array($curl3, array(
                CURLOPT_URL => 'https://api.bexio.com/2.0/contact',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($addressData),
                CURLOPT_HTTPHEADER => array(
                    'Accept: application/json',
                    'Authorization: Bearer eyJraWQiOiI2ZGM2YmJlOC1iMjZjLTExZTgtOGUwZC0wMjQyYWMxMTAwMDIiLCJhbGciOiJSUzI1NiJ9.eyJzdWIiOiJtYXJqYW5AYnVjaGVtLmNvbSIsImxvZ2luX2lkIjoiNmIyY2ZlM2UtNTVhYS00OTM0LThjY2ItNzgxMGU4ZjY3MTc4IiwiY29tcGFueV9pZCI6ImtmeWVqYnJxM3BsdiIsInVzZXJfaWQiOjMzNTkzNiwiYXpwIjoiZXZlcmxhc3QtdG9rZW4tb2ZmaWNlLWNsaWVudCIsInNjb3BlIjoib3BlbmlkIHByb2ZpbGUgZW1haWwgYWxsIHRlY2huaWNhbCIsImlzcyI6Imh0dHBzOlwvXC9pZHAuYmV4aW8uY29tIiwiZXhwIjozMjU3MzI1NDgwLCJpYXQiOjE2ODA1MjU0ODAsImNvbXBhbnlfdXNlcl9pZCI6MSwianRpIjoiZTE3ZWQ4YTMtZmRkYy00M2VkLTllMjItNjdiMDhjMWJjNjNjIn0.L4rNyP-ypCp890rWWc2cSCxcelf1FuY_rpvNI6YgiC0n69fgt_ilpEZmnZXuBSoVxzusWA9FuCk9MOWqjatFurU9BQy5kwt-sADDAgVWfoGFHiyZR1SFbq5P26zgofpwAA1zoVk7YudEEkl5SONTYpXXgQApmiSh5B7matjPGfBlk8qLhHxLFQYM3YPJaS-7Yp8kIvuOn6sSFs3WvuL-Wqfb96qQKxkB0oUEXrVy1aPxV7Xt4TC5edPGbD0CLX7SbGUZiNFWm59IQS9zeRIJPr5HufokhaeRXfull4xUy4uO-VxT535kAUxH_gBpjW8jhgC4TldkKMcIFsqoWKuI3sxL_8iLXCCF1dDytUUwrUoVvQs1NFV0WRpuE0R_IUQL6vlsFRbLvy-NLe0rV0r8Nei06cq-zcwNaGmnfUvUjSMTSwVpKtftxYXwhY8WipB4Jo4D_LSfUDx0kpOEAxFwwOB4J3EsO9TheQ9i8b3q2jhykrDUfKsiechH7uNiyvN-c2PL8NQd-rpDDZoJP9A3_ywZ2sOThDIEese6lrJtNibBY4i2ZIb7_FX5lg1du9KP3vgPLRgyYkoLe-zF5TLkZao75Y8KBU1YgteLRbE-VC4kUnTBrfmDukxqx8kvPP0ljp2on7HdpA7F1wj1sj_hvKlCcqxxZjwiBqsjjy41FUA',
                    'Content-Type: application/json'
                ),
            ));

            $response3 = curl_exec($curl3);

            curl_close($curl3);

            $contactRes = json_decode($response3);
            $contactId = $contactRes->id;

            // create order 
            $itemArr = [];
            foreach ($order->getAllVisibleItems() as $_item) {
                echo  " product id : " . $_item->getProductId();
                $product = $objectManager->create('Magento\Catalog\Model\Product')->load($_item->getProductId());
                $externalProductId = $product->getData('external_product_id');

                echo " external product id : " . $externalProductId;
                if ($externalProductId != '') {
                    $itemArr[] = [
                        "type" => "KbPositionArticle",
                        "amount" => $_item->getData('qty_ordered'),
                        "unit_id" => 1,
                        "account_id" => 101,
                        "tax_id" => 16,
                        "text" => $product->getShortDescription(),
                        "unit_price" => $_item->getPrice(),
                        "discount_in_percent" => "0.000000",
                        "is_optional" => false,
                        "article_id" => $externalProductId
                    ];
                } else {
                    $itemArr[] = [
                        "type" => "KbPositionCustom",
                        "amount" => $_item->getData('qty_ordered'),
                        "unit_id" => 1,
                        // "account_id" => 1,
                        "tax_id" => 16,
                        "text" => $product->getShortDescription(),
                        "unit_price" => $_item->getPrice(),
                        "discount_in_percent" => "0.000000",
                    ];
                }
            }

            $currency = 2;
            if ($order->getOrderCurrencyCode() == 'USD') {
                $currency = 3;
            }

            $orderArr = [
                "title" => "Mr.",
                "contact_id" => $contactId,
                "contact_sub_id" => null,
                "user_id" => $userId,
                "pr_project_id" => "",
                "logopaper_id" => 1,
                "language_id" => 4,
                "bank_account_id" => 1,
                "currency_id" => $currency,
                "payment_type_id" => 1,
                "header" => "Thank you very much for your inquiry. We would be pleased to make you the following offer:",
                "footer" => "We hope that our offer meets your expectations and will be happy to answer your questions.",
                "mwst_type" => 0,
                "mwst_is_net" => true,
                "show_position_taxes" => false,
                "is_valid_from" => date('Y-m-d'), //"Send today\'s date",
                "delivery_address_type" => 0,
                "api_reference" => $order->getIncrementId(),
                //   "template_slug" => "581a8010821e01426b8b456b",
                "positions" => $itemArr
            ];

            $curl5 = curl_init();
            curl_setopt_array($curl5, array(
                CURLOPT_URL => 'https://api.bexio.com/2.0/kb_order',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($orderArr),
                CURLOPT_HTTPHEADER => array(
                    'Accept: application/json',
                    'Authorization: Bearer eyJraWQiOiI2ZGM2YmJlOC1iMjZjLTExZTgtOGUwZC0wMjQyYWMxMTAwMDIiLCJhbGciOiJSUzI1NiJ9.eyJzdWIiOiJtYXJqYW5AYnVjaGVtLmNvbSIsImxvZ2luX2lkIjoiNmIyY2ZlM2UtNTVhYS00OTM0LThjY2ItNzgxMGU4ZjY3MTc4IiwiY29tcGFueV9pZCI6ImtmeWVqYnJxM3BsdiIsInVzZXJfaWQiOjMzNTkzNiwiYXpwIjoiZXZlcmxhc3QtdG9rZW4tb2ZmaWNlLWNsaWVudCIsInNjb3BlIjoib3BlbmlkIHByb2ZpbGUgZW1haWwgYWxsIHRlY2huaWNhbCIsImlzcyI6Imh0dHBzOlwvXC9pZHAuYmV4aW8uY29tIiwiZXhwIjozMjU3MzI1NDgwLCJpYXQiOjE2ODA1MjU0ODAsImNvbXBhbnlfdXNlcl9pZCI6MSwianRpIjoiZTE3ZWQ4YTMtZmRkYy00M2VkLTllMjItNjdiMDhjMWJjNjNjIn0.L4rNyP-ypCp890rWWc2cSCxcelf1FuY_rpvNI6YgiC0n69fgt_ilpEZmnZXuBSoVxzusWA9FuCk9MOWqjatFurU9BQy5kwt-sADDAgVWfoGFHiyZR1SFbq5P26zgofpwAA1zoVk7YudEEkl5SONTYpXXgQApmiSh5B7matjPGfBlk8qLhHxLFQYM3YPJaS-7Yp8kIvuOn6sSFs3WvuL-Wqfb96qQKxkB0oUEXrVy1aPxV7Xt4TC5edPGbD0CLX7SbGUZiNFWm59IQS9zeRIJPr5HufokhaeRXfull4xUy4uO-VxT535kAUxH_gBpjW8jhgC4TldkKMcIFsqoWKuI3sxL_8iLXCCF1dDytUUwrUoVvQs1NFV0WRpuE0R_IUQL6vlsFRbLvy-NLe0rV0r8Nei06cq-zcwNaGmnfUvUjSMTSwVpKtftxYXwhY8WipB4Jo4D_LSfUDx0kpOEAxFwwOB4J3EsO9TheQ9i8b3q2jhykrDUfKsiechH7uNiyvN-c2PL8NQd-rpDDZoJP9A3_ywZ2sOThDIEese6lrJtNibBY4i2ZIb7_FX5lg1du9KP3vgPLRgyYkoLe-zF5TLkZao75Y8KBU1YgteLRbE-VC4kUnTBrfmDukxqx8kvPP0ljp2on7HdpA7F1wj1sj_hvKlCcqxxZjwiBqsjjy41FUA',
                    'Content-Type: application/json'
                ),
            ));


            $response5 = curl_exec($curl5);

            $orderRes = json_decode($response5);
            curl_close($curl5);

            $order->setData('external_order_id', $orderRes->id);
            $order->save();


            $this->messageManager->addSuccess(__('Record has been sync successfully.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            
        } catch (\Exception $e) {
            echo $e->getMessage();
            // $logger->critical($e->getMessage());
            // $logger->error($e);
            $this->messageManager->addErrorMessage(__("Something went to wrong, Please try again."));
        }

        return $resultRedirect->setPath('sales/order/index');
    }
}
