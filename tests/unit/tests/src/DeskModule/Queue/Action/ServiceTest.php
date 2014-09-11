<?php
/**
 * @copyright Copyright (c) 2014 WebPT, INC
 */

namespace Unit\src\DeskModule\Queue\Action;

use DeskModule\Customer\CustomerDto;
use DeskModule\Model\Queue\Action;
use DeskModule\Queue\Action\Service as ActionService;
use DeskModule\Queue\Marshal\Company\CompanyToJson;
use DeskModule\Queue\Marshal\Customer\CustomerDtoToJson;
use EMRModel\Company\Company;
use InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;

class ServiceTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var ActionService
     */
    private $service;

    /** @var  Company */
    private $company;

    public function setUp()
    {
        $this->service = new ActionService();

        $this->service->setCompanyToJsonMarshaller(new CompanyToJson());
        $customerToJsonMarshaller = new CustomerDtoToJson();
        $customerToJsonMarshaller->setReflectionHydrator(new \Zend\Stdlib\Hydrator\Reflection());
        $this->service->setCustomerToJsonMarshaller($customerToJsonMarshaller);

        $mockedEntityManager = $this->getMock('Doctrine\ORM\EntityManager', array(), array(), '', false);

        $adapter = $this->getMock('EMRCore\DoctrineConnector\Adapter\Adapter');
        $adapter->expects($this->any())
            ->method('getEntityManager')
            ->will($this->returnValue($mockedEntityManager));

        $this->service->setDefaultMasterSlave($adapter);

        $this->company = new Company();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddCompanySyncThrowsExceptionWhenNoCompanyId()
    {
        $company = $this->getCompany()->setId(0);

        $this->getService()->addCompanySync($company);
    }

    public function testAddCustomerSyncReturnsAction() {
        $action = $this->getService()->addCustomerSync($this->getCustomerDto());

        $result = json_decode($action->getEntity());
        $this->assertEquals('webptId1', $result->webptId);
    }

    public function testAddCompanySyncReturnsPopulatedAction()
    {
        $company = $this->getCompany()
            ->setId(1)
            ->setName('foo');

        $json = json_encode(array(
            'id'   => $company->getId(),
            'name' => $company->getName()
        ));

        /** @var Action $action */
        $action = $this->getService()->addCompanySync($company);

        $this->assertEquals(Action::ENTITY_TYPE_COMPANY, $action->getEntityType());
        $this->assertEquals($json, $action->getEntity());
    }

    /**
     * @return CustomerDto
     */
    private function getCustomerDto()
    {
        static $id = 0;
        $id++;
        $customerDto = new CustomerDto;
        return $customerDto->setFirstName('FirstName_'.$id)
            ->setLastName('LastName_'.$id)
            ->setUserType('UserType_'.$id)
            ->setEmail('email_'.$id)
            ->setWebptCompanyId('webptCompanyId_'.$id)
            ->setDeskCompanyId('deskCompanyId_'.$id)
            ->setDeskId('deskId_'.$id)
            ->setWebptId('webptId'.$id);
    }


    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddCustomerSyncThrowsExceptionWhenNoWebptId()
    {
        $customer = $this->getCustomerDto();
        $customer->setWebptId(null);
        $this->getService()->addCustomerSync($customer);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddCustomerSyncThrowsExceptionWhenNoCompanyId()
    {
        $customer = $this->getCustomerDto();
        $customer->setDeskCompanyId(null);
        $customer->setWebptCompanyId(null);
        $this->getService()->addCustomerSync($customer);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddCustomerSyncThrowsExceptionWhenNoFirstName()
    {
        $customer = $this->getCustomerDto();
        $customer->setFirstName(null);
        $this->getService()->addCustomerSync($customer);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddCustomerSyncThrowsExceptionWhenNoLastName()
    {
        $customer = $this->getCustomerDto();
        $customer->setLastName(null);
        $this->getService()->addCustomerSync($customer);
    }

    public function testAddCustomerSyncNotThrowsExceptionWhenNoFirstNameAndNameCheckDisabled()
    {
        $customer = $this->getCustomerDto();
        $customer->setFirstName(null);

        $service = $this->getService();
        $service->setNameValidationEnabled(false);

        $exception = null;

        try
        {
            $service->addCustomerSync($customer);
        }
        catch(InvalidArgumentException $e)
        {
            $exception = $e;
        }

        $this->assertNull($exception);
    }

    public function testAddCustomerSyncNotThrowsExceptionWhenNoLastNameAndNameCheckDisabled()
    {
        $customer = $this->getCustomerDto();
        $customer->setLastName(null);

        $service = $this->getService();
        $service->setNameValidationEnabled(false);

        $exception = null;

        try
        {
            $service->addCustomerSync($customer);
        }
        catch(InvalidArgumentException $e)
        {
            $exception = $e;
        }

        $this->assertNull($exception);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddCustomerSyncThrowsExceptionWhenNoEmail()
    {
        $customer = $this->getCustomerDto();
        $customer->setEmail(null);
        $this->getService()->addCustomerSync($customer);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddCustomerSyncThrowsExceptionWhenNoUserType()
    {
        $customer = $this->getCustomerDto();
        $customer->setUserType(null);
        $this->getService()->addCustomerSync($customer);
    }

    public function testAddCustomerSyncReturnsPopulatedAction()
    {
        $customer = $this->getCustomerDto();

        /** @var Action $action */
        $action = $this->getService()->addCustomerSync($customer);

        $this->assertEquals(Action::ENTITY_TYPE_CUSTOMER, $action->getEntityType());
        $json = $action->getEntity();
        $this->assertContains($customer->getFirstName(), $json);
        $this->assertContains($customer->getLastName(), $json);
        $this->assertContains($customer->getEmail(), $json);
        $this->assertContains($customer->getDeskCompanyId(), $json);
        $this->assertContains($customer->getDeskId(), $json);
        $this->assertContains($customer->getUserType(), $json);
        $this->assertContains($customer->getWebptId(), $json);
    }

    public function testGetFindsRecordById() {
        $recordId = 1;

        /** @var ActionService|PHPUnit_Framework_MockObject_MockObject $sut */
        $sut = $this->getMock('DeskModule\Queue\Action\Service', array('getRepository'));

        $mockedEntityRepository = $this->getMock('Doctrine\ORM\EntityRepository', array(), array(), '', false);
        $mockedEntityRepository->expects($this->once())
            ->method('find')
            ->with($recordId)
            ->willReturn(Action::build()->setRecordId($recordId));

        $sut->expects($this->once())
            ->method('getRepository')
            ->willReturn($mockedEntityRepository);

        $result = $sut->get($recordId);
        $this->assertEquals($recordId, $result->getRecordId());
    }

    public function testGetRepository() {
        /** @var ActionService|PHPUnit_Framework_MockObject_MockObject $sut */
        $sut = $this->getMock('DeskModule\Queue\Action\Service', array('getDefaultMasterSlave'));

        $mockedEntityRepository = $this->getMock('\Doctrine\ORM\EntityManager', array(), array(), '', false);
        $mockedEntityRepository->expects($this->once())
            ->method('getRepository');

        $mockedAdapter = $this->getMock('EMRCore\DoctrineConnector\Adapter\Adapter',
            array('getEntityManager'), array(), '', false);
        $mockedAdapter->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($mockedEntityRepository);

        $sut->expects($this->once())
            ->method('getDefaultMasterSlave')
            ->willReturn($mockedAdapter);

        $sut->getRepository();
    }

    /**
     * @return ActionService
     */
    private function getService() {
        return $this->service;
    }

    /**
     * @return Company
     */
    private function getCompany() {
        return $this->company;
    }
} 