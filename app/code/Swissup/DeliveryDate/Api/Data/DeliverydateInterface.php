<?php
namespace Swissup\DeliveryDate\Api\Data;

interface DeliverydateInterface
{
    const ID = 'id';
    const QUOTE_ID = 'quote_id';
    const ORDER_ID = 'order_id';
    const COMMENT = 'comment';
    const DATE = 'date';
    const TIMERANGE = 'timerange';

    /**
     * Get id
     *
     * @return int
     */
    public function getId();

    /**
     * Get quote_id
     *
     * @return int
     */
    public function getQuoteId();

    /**
     * Get order_id
     *
     * @return int
     */
    public function getOrderId();

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment();

    /**
     * Get date
     *
     * @return string
     */
    public function getDate();

    /**
     * Get timerange
     *
     * @return string
     */
    public function getTimerange();


    /**
     * Set id
     *
     * @param int $id
     * @return \Swissup\Deliverydate\Api\Data\DeliverydateInterface
     */
    public function setId($id);

    /**
     * Set quote_id
     *
     * @param int $quoteId
     * @return \Swissup\Deliverydate\Api\Data\DeliverydateInterface
     */
    public function setQuoteId($quoteId);

    /**
     * Set order_id
     *
     * @param int $orderId
     * @return \Swissup\Deliverydate\Api\Data\DeliverydateInterface
     */
    public function setOrderId($orderId);

    /**
     * Set comment
     *
     * @param string $comment
     * @return \Swissup\Deliverydate\Api\Data\DeliverydateInterface
     */
    public function setComment($comment);

    /**
     * Set date
     *
     * @param string $date
     * @return \Swissup\Deliverydate\Api\Data\DeliverydateInterface
     */
    public function setDate($date);

    /**
     * Set timerange
     *
     * @param string $timerange
     * @return \Swissup\Deliverydate\Api\Data\DeliverydateInterface
     */
    public function setTimerange($timerange);
}
