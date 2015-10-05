<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Rest\Order;

use Orba\Payupl\Model\Client\Rest\Order;
use Orba\Payupl\Model\Client\Exception;

class ProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Processor
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_orderProcessor;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_orderProcessor = $this->getMockBuilder(\Orba\Payupl\Model\Order\Processor::class)->disableOriginalConstructor()->getMock();
        $this->_model = $objectManager->getObject(Processor::class, [
            'orderProcessor' => $this->_orderProcessor
        ]);
    }

    public function testProcessStatusChangeInvalid()
    {
        $this->setExpectedException(Exception::class, 'Invalid status.');
        $this->_model->processStatusChange(1, 'INVALID STATUS');
    }

    public function testProcessStatusChangeNotNewest()
    {
        $payuplOrderId = 'ABC';
        $status = Order::STATUS_CANCELLED;
        $this->_orderProcessor->expects($this->once())->method('processOld')->with(
            $this->equalTo($payuplOrderId),
            $this->equalTo($status)
        )->willReturn(true);
        $this->assertTrue($this->_model->processStatusChange($payuplOrderId, $status, 2.22, false));
    }

    public function testProcessStatusChangeNew()
    {
        $orderId = 1;
        $status = Order::STATUS_NEW;
        $this->_orderProcessor->expects($this->once())->method('processPending')->with(
            $this->equalTo($orderId),
            $this->equalTo($status)
        )->willReturn(true);
        $this->assertTrue($this->_model->processStatusChange($orderId, $status));
    }

    public function testProcessStatusChangePending()
    {
        $orderId = 1;
        $status = Order::STATUS_PENDING;
        $this->_orderProcessor->expects($this->once())->method('processPending')->with(
            $this->equalTo($orderId),
            $this->equalTo($status)
        )->willReturn(true);
        $this->assertTrue($this->_model->processStatusChange($orderId, $status));
    }

    public function testProcessStatusChangeCancelled()
    {
        $orderId = 1;
        $status = Order::STATUS_CANCELLED;
        $this->_orderProcessor->expects($this->once())->method('processHolded')->with(
            $this->equalTo($orderId),
            $this->equalTo($status)
        )->willReturn(true);
        $this->assertTrue($this->_model->processStatusChange($orderId, $status));
    }

    public function testProcessStatusChangeRejected()
    {
        $orderId = 1;
        $status = Order::STATUS_REJECTED;
        $this->_orderProcessor->expects($this->once())->method('processHolded')->with(
            $this->equalTo($orderId),
            $this->equalTo($status)
        )->willReturn(true);
        $this->assertTrue($this->_model->processStatusChange($orderId, $status));
    }

    public function testProcessStatusChangeWaiting()
    {
        $orderId = 1;
        $status = Order::STATUS_WAITING;
        $this->_orderProcessor->expects($this->once())->method('processWaiting')->with(
            $this->equalTo($orderId),
            $this->equalTo($status)
        )->willReturn(true);
        $this->assertTrue($this->_model->processStatusChange($orderId, $status));
    }

    public function testProcessStatusChangeCompleted()
    {
        $orderId = 1;
        $status = Order::STATUS_COMPLETED;
        $this->_orderProcessor->expects($this->once())->method('processCompleted')->with(
            $this->equalTo($orderId),
            $this->equalTo($status)
        )->willReturn(true);
        $this->assertTrue($this->_model->processStatusChange($orderId, $status));
    }
}