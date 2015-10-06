<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Resource;

use Magento\Framework\Model\Resource\Db\AbstractDb;
use Orba\Payupl\Model\Transaction as Model;

class Transaction extends AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_date;

    /**
     * @param \Magento\Framework\Model\Resource\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime $date
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\Resource\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime $date,
        $resourcePrefix = null
    )
    {
        parent::__construct(
            $context,
            $resourcePrefix
        );
        $this->_date = $date;
    }

    /**
     * @param int $orderId
     * @return string|false
     */
    public function getLastPayuplOrderIdByOrderId($orderId)
    {
        $adapter = $this->getReadConnection();
        $select = $adapter->select()
            ->from(
                ['main_table' => $this->_resources->getTableName('sales_payment_transaction')],
                ['txn_id']
            )->where('order_id = ?', $orderId)
            ->where('txn_type = ?', 'order')
            ->order('transaction_id ' . \Zend_Db_Select::SQL_DESC)
            ->limit(1);
        $row = $adapter->fetchRow($select);
        if ($row) {
            return $row['txn_id'];
        }
        return false;
    }

    /**
     * @param string $payuplOrderId
     * @return bool
     */
    public function checkIfNewestByPayuplOrderId($payuplOrderId)
    {
        $transactionTableName = $this->_resources->getTableName('sales_payment_transaction');
        $adapter = $this->getReadConnection();
        $select = $adapter->select()
            ->from(
                ['main_table' => $transactionTableName],
                ['transaction_id']
            )->joinLeft(
                ['t2' => $transactionTableName],
                't2.order_id = main_table.order_id AND t2.transaction_id > main_table.transaction_id',
                ['newer_id' => 't2.transaction_id']
            )->where('main_table.txn_id = ?', $payuplOrderId)
            ->limit(1);
        $row = $adapter->fetchRow($select);
        if ($row && is_null($row['newer_id'])) {
            return true;
        }
        return false;
    }

    /**
     * @param string $payuplOrderId
     * @return int|false
     */
    public function getOrderIdByPayuplOrderId($payuplOrderId)
    {
        return $this->_getOneFieldByAnother('order_id', 'txn_id', $payuplOrderId);
    }

    /**
     * @param string $payuplOrderId
     * @return string|false
     */
    public function getStatusByPayuplOrderId($payuplOrderId)
    {
        $serializedAdditionalInformation = $this->_getOneFieldByAnother('additional_information', 'txn_id', $payuplOrderId);
        if ($serializedAdditionalInformation) {
            $additionalInformation = unserialize($serializedAdditionalInformation);
            return $additionalInformation[\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS]['status'];
        }
        return false;
    }

    /**
     * @param int $orderId
     * @return int
     */
    public function getLastTryByOrderId($orderId)
    {
        $adapter = $this->getReadConnection();
        $select = $adapter->select()
            ->from(
                ['main_table' => $this->_resources->getTableName('sales_payment_transaction')],
                ['additional_information']
            )->where('order_id = ?', $orderId)
            ->where('txn_type = ?', 'order')
            ->order('transaction_id ' . \Zend_Db_Select::SQL_DESC)
            ->limit(1);
        $row = $adapter->fetchRow($select);
        if ($row) {
            $additionalInformation = unserialize($row['additional_information']);
            return $additionalInformation[\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS]['try'];
        }
        return 0;
    }

    protected function _construct() {}

    /**
     * @param string $getFieldName
     * @param string $byFieldName
     * @param mixed $value
     * @return mixed|false
     */
    protected function _getOneFieldByAnother($getFieldName, $byFieldName, $value)
    {
        $adapter = $this->getReadConnection();
        $select = $adapter->select()
            ->from(
                ['main_table' => $this->_resources->getTableName('sales_payment_transaction')],
                [$getFieldName]
            )->where($byFieldName . ' = ?', $value)
            ->limit(1);
        $row = $adapter->fetchRow($select);
        if ($row) {
            return $row[$getFieldName];
        }
        return false;
    }
}