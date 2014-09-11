<?php
/**
 *   @category WebPT
 *   @package DeskModule
 *   @copyright Copyright (c) 2014 WebPT, INC
 *   @author Tim Bradley (timothy.bradley@webpt.com)
 */

namespace Unit\src\DeskModule\Controller;

use DeskModule\Queue\Event\Manager as QueueEventManager;
use DeskModule\Queue\Lock\Service as DeskLockService;
use DeskModule\Queue\Service as DeskQueueService;
use DeskModule\Config\Config as DeskConfig;
use DeskModule\Controller\ConsoleController as DeskController;
use EMRCore\Zend\ServiceManager\Factory as ServiceManager;
use EMRCoreTest\Helper\Reflection;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Config\Config;
use Zend\Console\Request;

/**
 * Class ConsoleControllerTest
 * @package Unit\src\DeskModule\Controller
 */
class ConsoleControllerTest extends PHPUnit_Framework_TestCase {

    const MAX_TO_CONSUME = 100;

    /**
     * @return array
     */
    public function providerConsumeQueueAction()
    {
        return array(
            array(false, false),
            array(true, false),
            array(false, true),
            array(true, true),
        );
    }

    private function getController()
    {
        $controller = new DeskController();
        $controller->setConsole($this->getMock('\Zend\Console\Adapter\AdapterInterface'));
        $controller->setQueueEventManager(new QueueEventManager());
        $deskConfig = new DeskConfig();
        $deskConfig->setConfiguration(
            new Config(array('desk'=>array('queue'=>array('maxItemsToConsumePerRun'=>self::MAX_TO_CONSUME))))
        );
        $controller->setDeskConfig($deskConfig);

        return $controller;
    }

    /**
     * @param bool $verbose
     * @param bool $v
     * @dataProvider providerConsumeQueueAction
     */
    public function testConsumeQueueAction($verbose, $v)
    {
        $controller = $this->getController();

        /** @var Request|PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->getMockBuilder('Zend\Console\Request')
            ->disableOriginalConstructor()
            ->getMock();

        if($verbose)
        {
            $request->expects($this->exactly(1))->method('getParam')
                    ->with($this->equalTo('verbose'))
                    ->will($this->returnValue($verbose));
        }
        else
        {
            $request->expects($this->exactly(2))->method('getParam')
                    ->withConsecutive(
                        $this->equalTo('verbose'),
                        $this->equalTo('v')
                    )
                    ->will($this->returnValueMap(array(
                                array('verbose', $verbose),
                                array('v', $v),
                           )));
        }

        Reflection::set($controller, 'request', $request);

        /** @var DeskQueueService|PHPUnit_Framework_MockObject_MockObject $service */
        $service = $this->getMock('DeskModule\Queue\Service');

        $service->expects($this->once())->method('consumeQueue');
        $controller->setQueueService($service);

        $controller->consumeQueueAction();
    }

    public function testConsumeQueueActionCatchesExceptionWhenLockIsNotObtained() {
        $controller = $this->getController();

        /** @var Request|PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->getMockBuilder('Zend\Console\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $request->expects($this->exactly(1))->method('getParam')
            ->with($this->equalTo('verbose'))
            ->will($this->returnValue(true));

        Reflection::set($controller, 'request', $request);

        $fetchPlugin = $this->getMock('stdClass', array('fetch'));
        $fetchPlugin->expects($this->once())
            ->method('fetch')
            ->willReturn(array('ObtainedLock' => 0));

        $mockedConnection = $this->getMock('Doctrine\DBAL\Connection', array(), array(), '', false);
        $mockedConnection->expects($this->once())
            ->method('executeQuery')
            ->willReturn($fetchPlugin);

        $mockedEntityManager = $this->getMock('Doctrine\ORM\EntityManager', array(), array(), '', false);
        $mockedEntityManager->expects($this->once())
            ->method('getConnection')
            ->willReturn($mockedConnection);

        $mockedAdapter = $this->getMock('EMRCore\DoctrineConnector\Adapter\Adapter');
        $mockedAdapter->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($mockedEntityManager);

        $lockService = new DeskLockService();
        $lockService->setDefaultMasterSlave($mockedAdapter);

        /** @var DeskQueueService $service */
        $service = new DeskQueueService();
        $service->setLockService($lockService);

        $controller->setQueueService($service);

        $controller->consumeQueueAction();
    }
      /**
     * @param bool $verbose
     * @dataProvider providerConsumeQueueAction
     */
    public function testDeprecateAllCompaniesAction($verbose)
    {
        $controller = $this->getController();
        
           /** @var Request|PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->getMockBuilder('Zend\Console\Request')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \DeskModule\Client\Company\Deprecator|PHPUnit_Framework_MockObject_MockObject $service */
        $deprecator = $this->getMock('DeskModule\Client\Company\Deprecator');

        if($verbose)
        {
            $request->expects($this->exactly(1))->method('getParam')
                ->with($this->equalTo('verbose'))
                ->will($this->returnValue($verbose));

            $deprecator->expects($this->once())
                ->method('getCompanyClientEventManager')
                ->will($this->returnValue($eventManager = $this->getMock('DeskModule\Client\Company\Event\Manager')));
        }

        Reflection::set($controller, 'request', $request);

        $controller->setDeprecatorService($deprecator);

        $deprecator->expects($this->once())->method('deprecateAllCompanies');

        $controller->deprecateAllCompaniesAction();
    }

    public function testGetMaxItemsToConsume()
    {
        $controller = $this->getController();
        $result = Reflection::invoke($controller, 'getMaxItemsToConsume');
        $this->assertEquals(self::MAX_TO_CONSUME, $result);
    }


}
 