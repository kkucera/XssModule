<?php
/**
 * @copyright Copyright (c) 2014 WebPT, INC
 */

namespace tests\src\DeskModule\Queue;

use DeskModule\Customer\CustomerDto;
use DeskModule\Model\Queue\Action as ActionModel;
use DeskModule\Model\Queue\Archive;
use DeskModule\Model\Queue\Consumer;
use DeskModule\Queue\Marshal\Company\JsonToCompany;
use DeskModule\Queue\Event\Manager as QueueEventManager;
use DeskModule\Queue\Marshal\Customer\JsonToCustomerDto;
use DeskModule\Queue\Service;
use EMRCoreTest\Helper\Reflection;
use EMRModel\Company\Company;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Zend\Stdlib\Hydrator\Reflection as ReflectionHydrator;

class ServiceTest extends PHPUnit_Framework_TestCase
{

    /** @var int  */
    private static $recordId = 1;

    /** @var  Service */
    private $service;

    /** @var  ActionModel */
    protected $actionModel;

    public function setUp() {
        $this->service = new Service();
        $this->actionModel = new ActionModel();
        $this->getActionModel()
            ->setRecordId(self::$recordId)
            ->setAction(ActionModel::ACTION_SYNC)
            ->setEntityType(ActionModel::ENTITY_TYPE_COMPANY)
            ->setEntity(json_encode(array(
                'id'   => self::$recordId,
                'name' => 'foo'
            )));

        Reflection::set($this->getService(), 'consumer', new Consumer());

        $this->getService()->setCompanyService($this->getMock('DeskModule\Company\Company'));
        $this->getService()->setCustomerService($this->getMock('DeskModule\Customer\Customer'));

        $jsonToCompanyMarshaller = new JsonToCompany();
        $jsonToCompanyMarshaller->setReflectionHydrator(new ReflectionHydrator());
        $this->getService()->setJsonToCompanyMarshaller(
            $jsonToCompanyMarshaller
        );

        $this->getService()->setJsonToCustomerMarshaller(
            $this->getMock('DeskModule\Queue\Marshal\Customer\JsonToCustomerDto')
        );

        $this->getService()->setArchiveService($this->getMockedArchiveService());
        $this->getService()->setConsumerService($this->getConsumerService());
        $this->getService()->setQueueActionService($this->getMock('DeskModule\Queue\Action\Service'));
        $this->getService()->setTransactionEventManager($this->getMock('DeskModule\Transaction\Event\Manager'));
        $this->getService()->setQueueEventManager($this->getMock('DeskModule\Queue\Event\Manager'));
    }

    public function testConsumeQueue() {
        $mockedLockService = $this->getMock('DeskModule\Queue\Lock\Service');
        $this->getService()->setLockService($mockedLockService);

        $actionModel = new ActionModel();
        $actionModel->setAction(ActionModel::ACTION_SYNC)
            ->setEntityType(ActionModel::ENTITY_TYPE_COMPANY)
            ->setEntity('{}');

        $company = new Company();

        /** @var PHPUnit_Framework_MockObject_MockObject $queueActionService */
        $queueActionService = $this->getService()->getQueueActionService();
        $queueActionService->expects($this->at(0))
            ->method('getQueueCount')
            ->will($this->returnValue(1));
        $queueActionService->expects($this->at(1))
            ->method('getOneQueuedAction')
            ->willReturn($actionModel);
        $queueActionService->expects($this->at(2))
            ->method('getOneQueuedAction')
            ->willReturn(null);

        /** @var PHPUnit_Framework_MockObject_MockObject $companyMarshaller */
        $companyMarshaller = $this->getMock('DeskModule\Queue\Marshal\Company\JsonToCompany');
        $companyMarshaller->expects($this->once())
            ->method('marshall')
            ->with($actionModel->getEntity())
            ->will($this->returnValue($company));
        $this->getService()->setJsonToCompanyMarshaller($companyMarshaller);

        /** @var PHPUnit_Framework_MockObject_MockObject $companyService */
        $companyService = $this->getService()->getCompanyService();
        $companyService->expects($this->once())
            ->method('store')
            ->with($company);

        $this->getService()->consumeQueue();
    }

    public function testConsumeQueueSleepsWhenThrottleSecondsIsSet() {
        /** @var Service|PHPUnit_Framework_MockObject_MockObject $sut */
        $sut = $this->getMock('DeskModule\Queue\Service',
            array('getLockService', 'getQueueActionService', 'executeAction', 'archiveAction',
                  'getThrottleSeconds','attachTransactionListeners', 'setCurrentAction', 'getConsumer')
        );
        $sut->setQueueEventManager(new QueueEventManager);

        $mockedLockService = $this->getMock('DeskModule\Queue\Lock\Service');
        $sut->expects($this->any())
            ->method('getLockService')
            ->willReturn($mockedLockService);
        $consumer = new Consumer;
        $sut->expects($this->any())->method('getConsumer')->will($this->returnValue($consumer));

        $mockedQueueService = $this->getMock('DeskModule\Queue\Action\Service');
        $mockedQueueService->expects($this->at(0))
            ->method('getQueueCount')
            ->will($this->returnValue(1));
        $mockedQueueService->expects($this->at(1))
            ->method('getOneQueuedAction')
            ->willReturn($this->getActionModel());
        $mockedQueueService->expects($this->at(2))
            ->method('getOneQueuedAction');

        $sut->expects($this->once())
            ->method('setCurrentAction')
            ->with($this->getActionModel());
        $sut->expects($this->once())
            ->method('executeAction');
        $sut->expects($this->once())
            ->method('archiveAction');
        $sut->expects($this->once())
            ->method('getThrottleSeconds')
            ->willReturn(1);

        $sut->expects($this->any())
            ->method('getQueueActionService')
            ->willReturn($mockedQueueService);

        $mockedFunctionService = $this->getMock('EMRCore\PhpNet\Miscellaneous\Functions');
        $mockedFunctionService->expects($this->once())
            ->method('sleep');
        $sut->setFunctionService($mockedFunctionService);

        $sut->consumeQueue();
    }

    public function testExecuteActionExecutesCompanyAction() {
        $this->getActionModel()
            ->setEntityType(ActionModel::ENTITY_TYPE_COMPANY);

        $this->getService()->setCurrentAction($this->getActionModel());
        Reflection::invoke($this->getService(), 'executeAction', array());
    }

    public function testExecuteActionCallCustomerServiceStore() {
        $entity = 'a customer';
        $customerDto = new CustomerDto();
        $customerDto->setDeskCompanyId(1);

        /** @var \PHPUnit_Framework_MockObject_MockObject|\DeskModule\Queue\Marshal\Customer\JsonToCustomerDto $marshaller */
        $marshaller = $this->getService()->getJsonToCustomerMarshaller();
        $marshaller->expects($this->once())
            ->method('marshall')
            ->with($entity)
            ->will($this->returnValue($customerDto));

        $action = new ActionModel();
        $action->setAction(ActionModel::ACTION_SYNC)
            ->setEntityType(ActionModel::ENTITY_TYPE_CUSTOMER)
            ->setEntity($entity);

        /** @var \PHPUnit_Framework_MockObject_MockObject|\DeskModule\Customer\Customer $customerService */
        $customerService = $this->getService()->getCustomerService();
        $customerService->expects($this->once())->method('store')->with($customerDto);

        $this->getService()->setCurrentAction($action);
        Reflection::invoke($this->getService(), 'executeAction');
    }

    public function testLoadDeskCompanyId() {
        $customerDto = new CustomerDto();
        $customerDto->setWebptCompanyId($webptCompanyId = 321);

        $map = new \DeskModule\Model\Map\Company();
        $map->setWebptId($webptCompanyId)
            ->setDeskId($deskCompanyId = 832753);

        $companyMapService = $this->getMock('DeskModule\Map\Company');
        $companyMapService->expects($this->once())
            ->method('getByWebptId')
            ->with($webptCompanyId)
            ->will($this->returnValue($map));

        /** @var \PHPUnit_Framework_MockObject_MockObject|\DeskModule\Company\Company $companyService */
        $companyService = $this->getService()->getCompanyService();
        $companyService->expects($this->once())
            ->method('getCompanyMapService')
            ->will($this->returnValue($companyMapService));

        /** @var CustomerDto $result */
        $result = Reflection::invoke($this->getService(), 'loadDeskCompanyId', array($customerDto));
        $this->assertEquals($deskCompanyId, $result->getDeskCompanyId());
    }

    /**
     * @expectedException \DeskModule\Queue\Action\Exception\InvalidEntityException
     */
    public function testLoadDeskCompanyIdThrowsInvalidEntityWhenNoWebptId() {
        $customerDto = new CustomerDto();
        Reflection::invoke($this->getService(), 'loadDeskCompanyId', array($customerDto));
    }

    /**
     * @expectedException \DeskModule\Queue\Action\Exception\InvalidEntityException
     */
    public function testLoadDeskCompanyIdThrowsInvalidEntityWhenNoCompanyMap() {
        $customerDto = new CustomerDto();
        $customerDto->setWebptCompanyId($webptCompanyId = 321);

        $companyMapService = $this->getMock('DeskModule\Map\Company');
        $companyMapService->expects($this->once())
            ->method('getByWebptId')
            ->with($webptCompanyId)
            ->will($this->returnValue(null));

        /** @var \PHPUnit_Framework_MockObject_MockObject|\DeskModule\Company\Company $companyService */
        $companyService = $this->getService()->getCompanyService();
        $companyService->expects($this->once())
            ->method('getCompanyMapService')
            ->will($this->returnValue($companyMapService));

        Reflection::invoke($this->getService(), 'loadDeskCompanyId', array($customerDto));
    }

    /**
     * @expectedException \DeskModule\Queue\Action\Exception\InvalidEntityException
     */
    public function testExecuteActionThrowsExceptionIfNoActionIsSet() {
        $this->getActionModel()
            ->setEntityType(null);

        $this->getService()->setCurrentAction($this->getActionModel());
        Reflection::invoke($this->getService(), 'executeAction');
    }

    public function testExecuteCompanyActionStoresCompanyService() {
        $this->getActionModel()
            ->setEntityType(ActionModel::ENTITY_TYPE_COMPANY);

        Reflection::invoke($this->getService(), 'executeCompanyAction', array($this->getActionModel()));
    }

    /**
     * @expectedException \DeskModule\Queue\Action\Exception\InvalidActionException
     */
    public function testExecuteCompanyActionsThrowsExceptionIfActionIsNotSynced() {
        $this->getActionModel()
            ->setAction(null);

        Reflection::invoke($this->getService(), 'executeCompanyAction', array($this->getActionModel()));
    }

    /**
     * @expectedException \DeskModule\Queue\Action\Exception\InvalidActionException
     */
    public function testExecuteUserActionsThrowsExceptionIfActionIsNotSynced() {
        $this->getActionModel()
            ->setAction(null);

        Reflection::invoke($this->getService(), 'executeCustomerAction', array($this->getActionModel()));
    }

    public function testArchiveActionStoresAndDeletesAction() {
        $action = $this->getActionModel();
        $this->getService()->setQueueActionService($this->getMock('\DeskModule\Queue\Action\Service'));
        $this->getService()->setCurrentAction($action);
        Reflection::invoke($this->getService(), 'archiveAction');
    }

    public function testCloseConsumerStoresConsumer() {
        $consumer = new Consumer();
        Reflection::set($this->getService(), 'consumer', $consumer);
        $result = Reflection::invoke($this->getService(), 'closeConsumer');

        $this->assertEquals($consumer, $result);
    }

    public function testCloseConsumerReturnsNullIfConsumerNotSet() {
        Reflection::set($this->getService(), 'consumer', null);
        $result = Reflection::invoke($this->getService(), 'closeConsumer');

        $this->assertNull($result);
    }

    public function testGetConsumerReturnsConsumerModelIfSetToConsumer() {
        $consumer = new Consumer();
        Reflection::set($this->getService(), 'consumer', $consumer);
        $result = Reflection::invoke($this->getService(), 'getConsumer');

        $this->assertEquals($consumer, $result);
    }

    public function testGetConsumerReturnsConsumerIfConsumerIsEmpty() {
        Reflection::set($this->getService(), 'consumer', null);
        $result = Reflection::invoke($this->getService(), 'getConsumer');

        $this->assertInstanceOf('DeskModule\Model\Queue\Consumer', $result);
    }

    public function testGetThrottleSecondsReturnsSetClassMemberValueWhenMinSecondsIsOfAHigherValue() {
        $expectedSeconds = 2;
        Reflection::set($this->getService(), 'throttleSeconds', $expectedSeconds);

        $actualSeconds = $this->getService()->getThrottleSeconds(3);

        $this->assertEquals($expectedSeconds, $actualSeconds);
    }

    public function testGetThrottleSecondsReturnsMinSecondsPassedWhenValueIsHigherThanSetClassMember() {
        $expectedSeconds = 1;
        Reflection::set($this->getService(), 'throttleSeconds', 2);

        $actualSeconds = $this->getService()->getThrottleSeconds($expectedSeconds);

        $this->assertEquals($expectedSeconds, $actualSeconds);
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockedQueueService() {
        $mockedQueueService = $this->getMock('DeskModule\Queue\Action\Service');
        $mockedQueueService->expects($this->at(0))
            ->method('getOneQueuedAction')
            ->willReturn($this->getActionModel());
        $mockedQueueService->expects($this->at(1))
            ->method('getOneQueuedAction')
            ->willReturn(null);

        return $mockedQueueService;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockedArchiveService() {
        $mockedArchiveService = $this->getMock('\DeskModule\Queue\Archive\Service');
        $mockedArchiveService->expects($this->any())
            ->method('create')
            ->willReturn(new Archive());

        return $mockedArchiveService;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getConsumerService() {
        $mockedConsumerService = $this->getMock('\DeskModule\Queue\Consumer\Service');
        $mockedConsumerService->expects($this->any())
            ->method('create')
            ->willReturn(new Consumer());

        return $mockedConsumerService;
    }

    /**
     * @return Service
     */
    private function getService() {
        return $this->service;
    }

    /**
     * @return ActionModel
     */
    private function getActionModel() {
        return $this->actionModel;
    }
} 