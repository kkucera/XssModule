<?php
/**
 * Created by PhpStorm
 * @User joshuapacheco
 * @category WebPT
 * @package DeskModule
 * @copyright Copyright (c) 2014 WebPT, INC
 */

namespace Unit\src\DeskModule\Queue\Lock;


use DeskModule\Queue\Lock\Service;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;

class ServiceTest extends PHPUnit_Framework_TestCase {

    /** @var  Service */
    private $service;

    public function setUp() {
        parent::setUp();

        $this->service = new Service();
    }

    public function testObtainLock() {
        $adapter = $this->getMockedDefaultMasterSlaveFetchForLock(1);
        $this->getService()->setDefaultMasterSlave($adapter);

        $this->getService()->obtainLock();
    }

    /**
     * @expectedException \DeskModule\Queue\Lock\LockFailedException
     */
    public function testObtainLockThrowsExceptionIfNoLockIsFound() {
        $adapter = $this->getMockedDefaultMasterSlaveFetchForLock(0);
        $this->getService()->setDefaultMasterSlave($adapter);

        $this->getService()->obtainLock();
    }

    /**
     * @param mixed $lockValue
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockedDefaultMasterSlaveFetchForLock($lockValue) {
        $fetchPlugin = $this->getMockForAbstractClass('\Doctrine\DBAL\Driver\ResultStatement');

        $fetchPlugin->expects($this->once())
            ->method('fetch')
            ->willReturn(array('ObtainedLock' => $lockValue));

        $mockedConnection = $this->getMock('Doctrine\DBAL\Connection', array(), array(), '', false);
        $mockedConnection->expects($this->once())
            ->method('executeQuery')
            ->willReturn($fetchPlugin);

        $mockedEntityManager = $this->getMock('Doctrine\ORM\EntityManager', array(), array(), '', false);
        $mockedEntityManager->expects($this->once())
            ->method('getConnection')
            ->willReturn($mockedConnection);

        $adapter = $this->getMock('EMRCore\DoctrineConnector\Adapter\Adapter');
        $adapter->expects($this->any())
            ->method('getEntityManager')
            ->will($this->returnValue($mockedEntityManager));

        return $adapter;
    }

    /**
     * @return Service
     */
    private function getService() {
        return $this->service;
    }
}
 