<?php
/**
 * @copyright Copyright (c) 2014 WebPT, INC
 */

namespace Unit\src\DeskModule\Queue\Archive;

use DeskModule\Model\Queue\Consumer;
use DeskModule\Queue\Consumer\Service as ConsumerService;
use EMRCoreTest\Helper\Reflection;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;

class ServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ConsumerService
     */
    private $service;

    /** @var Consumer */
    private static $consumer;

    public function setUp()
    {
        self::$consumer = new Consumer();

        $this->service = new ConsumerService();

        $mockedEntityManager = $this->getMock('Doctrine\ORM\EntityManager', array(), array(), '', false);

        $adapter = $this->getMock('EMRCore\DoctrineConnector\Adapter\Adapter');
        $adapter->expects($this->any())
            ->method('getEntityManager')
            ->will($this->returnValue($mockedEntityManager));
        $this->service->setDefaultMasterSlave($adapter);
    }

    public function testCreateReturnsInitializedModel()
    {
        $model = $this->getService()->create();

        $this->assertInstanceOf('DeskModule\Model\Queue\Consumer', $model);
    }

    public function testStorePersists() {
        $result = $this->getService()->store(self::$consumer);

        $this->assertEquals(self::$consumer, $result);
    }

    public function testGetRepositoryReturnsConsumerRepository()
    {
        /** @var ConsumerService|PHPUnit_Framework_MockObject_MockObject $sut */
        $sut = $this->getMock('DeskModule\Queue\Consumer\Service', array('getDefaultMasterSlave'));

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

    public function testPersist() {
        /** @var ConsumerService|PHPUnit_Framework_MockObject_MockObject $sut */
        $sut = $this->getMock('DeskModule\Queue\Consumer\Service', array('getDefaultMasterSlave'));

        $mockedEntityRepository = $this->getMock('\Doctrine\ORM\EntityManager', array(), array(), '', false);
        $mockedEntityRepository->expects($this->once())
            ->method('persist')
            ->with(self::$consumer);
        $mockedEntityRepository->expects($this->once())
            ->method('flush')
            ->with(self::$consumer);

        $mockedAdapter = $this->getMock('EMRCore\DoctrineConnector\Adapter\Adapter',
            array('getEntityManager'), array(), '', false);
        $mockedAdapter->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($mockedEntityRepository);

        $sut->expects($this->once())
            ->method('getDefaultMasterSlave')
            ->willReturn($mockedAdapter);

        Reflection::invoke($sut, 'persist', array(self::$consumer));
    }

    /**
     * @return ConsumerService
     */
    private function getService() {
        return $this->service;
    }
} 