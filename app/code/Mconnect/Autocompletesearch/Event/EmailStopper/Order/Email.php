<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mconnect\Autocompletesearch\Event\EmailStopper\Order;


class Email implements \Magento\Framework\Event\ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        \Magento\Framework\App\ObjectManager::getInstance()
        ->get('Psr\Log\LoggerInterface')
        ->debug('My File Executed...................... ');
    try{
        $order = $observer->getEvent()->getOrder();
        $this->_current_order = $order;

        $payment = $order->getPayment()->getMethodInstance()->getCode();

        if($payment == "cashondelivery"){
            $this->stopNewOrderEmail($order);
        }
    }
    catch (\ErrorException $ee){
        \Magento\Framework\App\ObjectManager::getInstance()
        ->get('Psr\Log\LoggerInterface')
        ->debug('An error has occured : '.$ee->getMessage());
    }
    catch (\Exception $ex)
    {
        \Magento\Framework\App\ObjectManager::getInstance()
        ->get('Psr\Log\LoggerInterface')
        ->debug('An error has occured : '.$ex->getMessage());
    }
    catch (\Error $error){
        \Magento\Framework\App\ObjectManager::getInstance()
        ->get('Psr\Log\LoggerInterface')
        ->debug('An error has occured : '.$error->getMessage());
    }

}

    public function stopNewOrderEmail(\Magento\Sales\Model\Order $order){
        $order->setCanSendNewEmailFlag(false);
        $order->setSendEmail(false);
        try{
            $order->save();
        }
        catch (\ErrorException $ee){
            \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Psr\Log\LoggerInterface')
            ->debug('An error has occured : '.$ee->getMessage());
        }
        catch (\Exception $ex)
        {
            \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Psr\Log\LoggerInterface')
            ->debug('An error has occured : '.$ex->getMessage());
        }
        catch (\Error $error){
            \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Psr\Log\LoggerInterface')
            ->debug('An error has occured : '.$error->getMessage());
        }
    }
} 