<?php
/**
 * @copyright Copyright (c) 2014 WebPT, INC
 */

namespace Unit\src\DeskModule\Customer;

use DeskModule\Client\Customer\Event\Manager;
use DeskModule\Customer\Customer as CustomerService;
use DeskModule\Customer\CustomerDto;
use DeskModule\Model\Map\Customer as CustomerMap;

class CustomerTest extends \PHPUnit_Framework_TestCase
{

    /** @var  CustomerService */
    private $sut;

    protected function setUp()
    {
        parent::setUp();

        $sut = new CustomerService();
        $sut->setCustomerMapService($this->getMock('DeskModule\Map\Customer'));
        $sut->setCustomerClientService($this->getMock('DeskModule\Client\Customer\Customer'));
        $this->sut = $sut;
    }

    /**
     * @return int
     */
    private function getId()
    {
        static $id = 0;
        return ++$id;
    }

    public function testStoreCallsCreateWhenNoMap()
    {
        $customer = new CustomerDto();
        $customer->setWebptId(1);

        /** @var \PHPUnit_Framework_MockObject_MockObject $clientService */
        $clientService = $this->sut->getCustomerClientService();
        $clientService->expects($this->once())->method('create')->with($customer);

        $this->sut->store($customer);
    }

    public function testStoreCallsUpdateWhenNoMap()
    {
        $customer = new CustomerDto();
        $customer->setWebptId($webptId = 32);

        /** @var \PHPUnit_Framework_MockObject_MockObject $customerMap */
        $customerMap = $this->sut->getCustomerMapService();
        $customerMap->expects($this->once())
            ->method('getByWebptId')
            ->with($webptId)
            ->will($this->returnValue($map = new CustomerMap));

        /** @var \PHPUnit_Framework_MockObject_MockObject $clientService */
        $clientService = $this->sut->getCustomerClientService();
        $clientService->expects($this->once())->method('update')->with($customer);

        $this->sut->store($customer);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testStoreThrowsCustomerNotFoundException()
    {
        $customer = new CustomerDto();
        $this->sut->store($customer);
    }

    public function testNotCreateDueToInvalidResponse()
    {
        $customer = new CustomerDto;
        $customer->setWebptId($webptId = $this->getId());

        /** @var \PHPUnit_Framework_MockObject_MockObject $mapService */
        $mapService = $this->sut->getCustomerMapService();
        $mapService->expects($this->once())->method('getByWebptId')
            ->with($webptId)->will($this->returnValue(null));

        $clientCommand = $this->getMock('\DeskModule\Client\ClientCommand', array(), array(), '', false);

        /** @var \PHPUnit_Framework_MockObject_MockObject|\DeskModule\Client\Customer\Customer $clientService */
        $clientService = $this->getMockBuilder('DeskModule\Client\Customer\Customer')
            ->setMethods(array('getCustomerToCreateCommandMarshaller'))
            ->getMock();
        $clientService->expects($this->once())
            ->method('getCustomerToCreateCommandMarshaller')
            ->will($this->returnValue(
                $marshaller = $this->getMock('\DeskModule\Client\Customer\Marshaller\CustomerToCreateCommand')
            ));
        $clientService->setCustomerClientEventManager(new Manager());

        $this->sut->setCustomerClientService($clientService);

        $marshaller->expects($this->once())->method('marshall')
            ->with($customer)->will($this->returnValue($clientCommand));

        $clientCommand->expects($this->once())->method('execute')
            ->will($this->returnValue($this->getMock('\Desk\Relationship\Resource\Model')));

        //$clientService->setLogger($this->getMock('\Logger', array(), array(), '', false));

        // Ensure map was not created.
        $mapService->expects($this->never())->method('create');

        $this->sut->store($customer);
    }

} 