<?php
namespace Swissup\CheckoutFields\Plugin\Ui\Component;

use Magento\Ui\Component\Listing\Columns as ListingColumns;
use Swissup\CheckoutFields\Model\ResourceModel\Field\CollectionFactory;
use Swissup\CheckoutFields\Ui\Component\OrderColumnFactory;

class OrderListingColumns
{

    /**
     * Checkout fields collection factory
     * @var CollectionFactory
     */
    protected $fieldsCollectionFactory;

    /**
     * @var OrderColumnFactory
     */
    protected $columnFactory;

    /**
     * Checkout fields helper
     * @var \Swissup\CheckoutFields\Helper\Data
     */
    protected $helper;

    /**
     * @param CollectionFactory $fieldsCollectionFactory
     * @param OrderColumnFactory $columnFactory
     * @param \Swissup\CheckoutFields\Helper\Data $helper
     */
    public function __construct(
        CollectionFactory $fieldsCollectionFactory,
        OrderColumnFactory $columnFactory,
        \Swissup\CheckoutFields\Helper\Data $helper
    ) {
        $this->fieldsCollectionFactory = $fieldsCollectionFactory;
        $this->columnFactory = $columnFactory;
        $this->helper = $helper;
    }

    /**
     * @param ListingColumns $subject
     */
    public function beforePrepare(ListingColumns $subject)
    {
        if ($this->helper->isEnabled() && $this->isOrderGrid($subject)) {
            $fields = $this->fieldsCollectionFactory->create()
                ->addUsedInGridFilter(1)
                ->addOrder(
                    \Swissup\CheckoutFields\Api\Data\FieldInterface::SORT_ORDER,
                    \Magento\Framework\Data\Collection::SORT_ORDER_ASC
                );

            foreach ($fields as $field) {
                $column = $this->columnFactory->create(
                    $field,
                    $subject->getContext(),
                    array_merge([
                        'filter' => 'text',
                        'sortable' => true,
                        'add_field' => false,
                        'visible' => true,
                    ], $this->getColumnSettings($field))
                );
                $column->prepare();
                $attributeCode = $field->getAttributeCode();
                $subject->addComponent($attributeCode, $column);
            }
        }
    }

    /**
     * @param ListingColumns $columns
     * @return bool
     */
    protected function isOrderGrid($columns)
    {
        return $columns->getName() == 'sales_order_columns';
    }

    private function getColumnSettings($field)
    {
        $settings = [
            'filter' => 'text',
        ];

        switch ($field->getFrontendInput()) {
            case 'date':
                $settings['filter'] = 'dateRange';
                break;
            case 'boolean':
            case 'select':
            case 'multiselect':
                $settings['filter'] = 'select';
                break;
        }

        return $settings;
    }
}
