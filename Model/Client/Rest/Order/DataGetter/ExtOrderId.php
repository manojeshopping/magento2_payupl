<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Rest\Order\DataGetter;

class ExtOrderId
{
    /**
     * @var \Orba\Payupl\Model\Resource\Transaction\CollectionFactory
     */
    protected $_transactionCollectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_dateTime;

    public function __construct(
        \Orba\Payupl\Model\Resource\Transaction\CollectionFactory $transactionCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    )
    {
        $this->_transactionCollectionFactory = $transactionCollectionFactory;
        $this->_dateTime = $dateTime;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    public function generate(\Magento\Sales\Model\Order $order)
    {
        /**
         * @var $transactionCollection \Orba\Payupl\Model\Resource\Transaction\Collection
         * @var $transaction \Orba\Payupl\Model\Transaction
         */
        $transactionCollection = $this->_transactionCollectionFactory->create();
        $transactionCollection
            ->addFieldToFilter('order_id', $order->getId())
            ->setOrder('try', \Magento\Framework\Data\Collection::SORT_ORDER_DESC);
        $transaction = $transactionCollection->getFirstItem();
        if ($transaction->getId()) {
            $try = $transaction->getTry() + 1;
        } else {
            $try = 1;
        }
        return $order->getIncrementId() . ':' . $this->_dateTime->timestamp() . ':' . $try;
    }
}