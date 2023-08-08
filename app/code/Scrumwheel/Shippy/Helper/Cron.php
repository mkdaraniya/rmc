<?php
declare(strict_types=1);

namespace Scrumwheel\Shippy\Helper;

use Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory;
use Magento\Cron\Model\Schedule;
use Magento\Cron\Model\ScheduleFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Cron extends AbstractHelper
{
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    protected ScheduleFactory     $scheduleFactory;
    protected CollectionFactory   $collectionFactory;
    protected DateTime            $dateTime;
    protected SerializerInterface $serializer;

    public function __construct(
        Context $context,
        ScheduleFactory $scheduleFactory,
        CollectionFactory $collectionFactory,
        DateTime $dateTime,
        SerializerInterface $serializer
    ) {
        parent::__construct($context);
        $this->scheduleFactory   = $scheduleFactory;
        $this->collectionFactory = $collectionFactory;
        $this->dateTime          = $dateTime;
        $this->serializer        = $serializer;
    }

    public function create(string $jobCode, int $scheduleAt = null, array $arguments = []): void
    {
        $schedule = $this->scheduleFactory->create()
            ->setJobCode($jobCode)
            ->setStatus(Schedule::STATUS_PENDING)
            ->setCreatedAt(date(self::DATE_FORMAT, $this->dateTime->gmtTimestamp()))
            ->setScheduledAt(date(self::DATE_FORMAT, $scheduleAt ?? $this->dateTime->gmtTimestamp()));

        if ($arguments) {
            $schedule->setArguments($this->serializer->serialize($arguments));
        }

        $schedule->save();
    }

    /**
     * @return Schedule[]
     */
    public function search(array $filters): array
    {
        $collection = $this->collectionFactory->create();
        foreach ($filters as $field => $condition) {
            $collection->addFieldToFilter($field, $condition);
        }

        return $collection->getItems();
    }

    public function delete(Schedule $schedule): void
    {
        $schedule->delete();
    }
}