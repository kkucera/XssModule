<?php
/**
 * 
 * 
 * @copyright Copyright (c) 2014 WebPT, INC
 */

namespace Unit\src\DeskModule\Client\Customer;

use DeskModule\Client\Customer\Customer as CustomerClient;
use EMRCoreTest\Helper\Reflection;
use DeskModule\Customer\CustomerDto;
use Zend\EventManager\Event;

/**
 *
 *
 * @category WebPT
 * @package
 */
class CustomerTest extends \PHPUnit_Framework_TestCase
{
    /** @var  CustomerClient */
    private $sut;

    protected function setUp()
    {
        parent::setUp();

        $this->sut = new CustomerClient;
        $this->sut->setCustomerClientEventManager($this->getMock('\DeskModule\Client\Customer\Event\Manager'));
        $this->sut->setCustomerToCreateCommandMarshaller($this->getMock('\DeskModule\Client\Customer\Marshaller\CustomerToCreateCommand'));
        $this->sut->setCustomerToUpdateCommandMarshaller($this->getMock('\DeskModule\Client\Customer\Marshaller\CustomerToUpdateCommand'));
        $this->sut->setDeskIdToShowCommandMarshaller($this->getMock('\DeskModule\Client\Customer\Marshaller\DeskIdToShowCommand'));
        $this->sut->setLogger($this->getMock('\Logger', array(), array(), '', false));
    }

    public function testTriggerSetsTarget()
    {
        $event = new Event;

        $this->assertNull($event->getTarget());

        Reflection::invoke($this->sut, 'trigger', array($event));

        $this->assertSame($this->sut, $event->getTarget());
    }

    public function testCreateTriggersEvents()
    {
        $company = new CustomerDto;

        $clientCommand = $this->getMock('\DeskModule\Client\ClientCommand', array(), array(), '', false);

        /** @var \PHPUnit_Framework_MockObject_MockObject $marshaller */
        $marshaller = $this->sut->getCustomerToCreateCommandMarshaller();
        $marshaller->expects($this->once())->method('marshall')
            ->with($company)->will($this->returnValue($clientCommand));

        /** @var \PHPUnit_Framework_MockObject_MockObject $eventManager */
        $eventManager = $this->sut->getCustomerClientEventManager();
        $eventManager->expects($this->at(0))->method('trigger')
            ->with($this->isInstanceOf('\DeskModule\Client\Customer\Event\Response\Create\PreExecute'));

        $clientCommand->expects($this->once())->method('execute')
            ->will($this->returnValue($expected = $this->getMock('\Desk\Relationship\Resource\Model')));

        $eventManager->expects($this->at(1))->method('trigger')
            ->with($this->isInstanceOf('\DeskModule\Client\Customer\Event\Response\Create\PostExecute'));

        $actual = $this->sut->create($company);
        $this->assertSame($expected, $actual);
    }

    public function testGetByDeskIdTriggersEvents()
    {
        $deskId = 1001;

        $clientCommand = $this->getMock('\DeskModule\Client\ClientCommand', array(), array(), '', false);

        /** @var \PHPUnit_Framework_MockObject_MockObject $marshaller */
        $marshaller = $this->sut->getDeskIdToShowCommandMarshaller();
        $marshaller->expects($this->once())->method('marshall')
            ->with($deskId)->will($this->returnValue($clientCommand));

        /** @var \PHPUnit_Framework_MockObject_MockObject $eventManager */
        $eventManager = $this->sut->getCustomerClientEventManager();
        $eventManager->expects($this->at(0))->method('trigger')
            ->with($this->isInstanceOf('\DeskModule\Client\Customer\Event\Response\Read\PreExecute'));

        $clientCommand->expects($this->once())->method('execute')
            ->will($this->returnValue($expected = $this->getMock('\Desk\Relationship\Resource\Model')));

        $eventManager->expects($this->at(1))->method('trigger')
            ->with($this->isInstanceOf('\DeskModule\Client\Customer\Event\Response\Read\PostExecute'));

        $actual = $this->sut->getByDeskId($deskId);
        $this->assertSame($expected, $actual);
    }

    public function testUpdateTriggersEvents()
    {
        $company = new CustomerDto;
        $deskId = 2001;

        $clientCommand = $this->getMock('\DeskModule\Client\ClientCommand', array(), array(), '', false);

        /** @var \PHPUnit_Framework_MockObject_MockObject $marshaller */
        $marshaller = $this->sut->getCustomerToUpdateCommandMarshaller();
        $marshaller->expects($this->once())->method('marshall')
            ->with($company)->will($this->returnValue($clientCommand));

        /** @var \PHPUnit_Framework_MockObject_MockObject $eventManager */
        $eventManager = $this->sut->getCustomerClientEventManager();
        $eventManager->expects($this->at(0))->method('trigger')
            ->with($this->isInstanceOf('\DeskModule\Client\Customer\Event\Response\Update\PreExecute'));

        $clientCommand->expects($this->once())->method('execute')
            ->will($this->returnValue($expected = $this->getMock('\Desk\Relationship\Resource\Model')));

        $eventManager->expects($this->at(1))->method('trigger')
            ->with($this->isInstanceOf('\DeskModule\Client\Customer\Event\Response\Update\PostExecute'));

        $actual = $this->sut->update($company, $deskId);
        $this->assertSame($expected, $actual);
    }
} 