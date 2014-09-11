<?php
/**
 * @copyright Copyright (c) 2014 WebPT, INC
 */

namespace Unit\src\DeskModule\Queue\Consumer;

use DeskModule\Model\Queue\Action;
use DeskModule\Model\Queue\Archive;
use DeskModule\Queue\Archive\Service as ArchiveService;
use EMRCoreTest\Helper\Reflection;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;

class ServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ArchiveService
     */
    private $service;

    /** @var  Archive */
    private static $archive;

    public function setUp()
    {
        self::$archive = $this->getArchiveModel()->setRecordId(1)
            ->setEntity('foo')
            ->setEntityType(Action::ENTITY_TYPE_COMPANY)
            ->setAction('bar');

        $this->service = new ArchiveService();

        $mockedEntityManager = $this->getMock('Doctrine\ORM\EntityManager', array(), array(), '', false);

        $adapter = $this->getMock('EMRCore\DoctrineConnector\Adapter\Adapter');
        $adapter->expects($this->any())
            ->method('getEntityManager')
            ->will($this->returnValue($mockedEntityManager));
        $this->service->setDefaultMasterSlave($adapter);
    }

    public function testCreateReturnsLoadedArchiveModel()
    {
        $action = $this->getActionModel()->setRecordId(self::$archive->getRecordId())
            ->setEntity(self::$archive->getEntity())
            ->setEntityType(self::$archive->getEntityType())
            ->setAction(self::$archive->getAction());

        $archive = $this->getService()->create($action);

        $this->assertEquals(self::$archive, $archive);
    }

    public function testStorePersists()
    {
        $result = $this->getService()->store(self::$archive);

        $this->assertEquals(self::$archive, $result);
    }

    public function testGetRepositoryReturnsArchiveRepository()
    {
        /** @var ArchiveService|PHPUnit_Framework_MockObject_MockObject $sut */
        $sut = $this->getMock('DeskModule\Queue\Archive\Service', array('getDefaultMasterSlave'));

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
        /** @var ArchiveService|PHPUnit_Framework_MockObject_MockObject $sut */
        $sut = $this->getMock('DeskModule\Queue\Archive\Service', array('getDefaultMasterSlave'));

        $mockedEntityRepository = $this->getMock('\Doctrine\ORM\EntityManager', array(), array(), '', false);
        $mockedEntityRepository->expects($this->once())
            ->method('persist')
            ->with(self::$archive);
        $mockedEntityRepository->expects($this->once())
            ->method('flush')
            ->with(self::$archive);

        $mockedAdapter = $this->getMock('EMRCore\DoctrineConnector\Adapter\Adapter',
            array('getEntityManager'), array(), '', false);
        $mockedAdapter->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($mockedEntityRepository);

        $sut->expects($this->once())
            ->method('getDefaultMasterSlave')
            ->willReturn($mockedAdapter);

        Reflection::invoke($sut, 'persist', array(self::$archive));
    }

    /**
     * @return Action
     */
    private function getActionModel() {
        return new Action();
    }

    /**
     * @return Archive
     */
    private function getArchiveModel() {
        return new Archive();
    }

    /**
     * @return ArchiveService
     */
    private function getService() {
        return $this->service;
    }
} 