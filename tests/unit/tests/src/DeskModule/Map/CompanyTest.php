<?php
/**
 *
 *
 * @copyright Copyright (c) 2014 WebPT, INC
 */

namespace Unit\src\DeskModule\Map;

use DeskModule\Map\Company as CompanyMapService;
use DeskModule\Model\Map\Company as CompanyMapModel;
use DeskModule\Model\Map\Repository\Company as Repository;
use PHPUnit_Framework_MockObject_MockObject;

/**
 *
 *
 * @category WebPT
 * @package
 * @group Map
 */
class CompanyTest extends \PHPUnit_Framework_TestCase
{

    /** @var  CompanyMapService */

    private $sut;

    public function setUp()
    {
        parent::setUp();
        $this->sut = new CompanyMapService();
        /** @var Repository $mockRepository */
        $mockRepository = $this->getMockRepository();
        $this->sut->setRepository($mockRepository);
    }

    /**
     * @return int
     */
    private function getId()
    {
        static $id = 1000;
        return ++$id;
    }

    public function testCreate()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject $mockRepository */
        $mockRepository = $this->sut->getRepository();

        $mockRepository->expects($this->once())
            ->method('store')
            ->will($this->returnValue(null));
        /** @var Repository $mockRepository */

        $this->assertNull($this->sut->create(new CompanyMapModel()));
    }

    public function testUpdate()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject $mockRepository */
        $mockRepository = $this->sut->getRepository();

        $mockRepository->expects($this->once())
            ->method('store')
            ->will($this->returnValue(null));
        /** @var Repository $mockRepository */

        $this->sut->setRepository($mockRepository);

        $this->assertNull($this->sut->update(new CompanyMapModel()));
    }

    public function testDelete()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject $mockRepository */
        $mockRepository = $this->sut->getRepository();

        $mockRepository->expects($this->once())
            ->method('delete')
            ->will($this->returnValue(null));
        /** @var Repository $mockRepository */

        $this->sut->setRepository($mockRepository);

        $this->assertNull($this->sut->delete(new CompanyMapModel()));
    }

    public function testGetById()
    {
        $id = $this->getId();
        $map = new CompanyMapModel;

        /** @var PHPUnit_Framework_MockObject_MockObject $mockRepository */
        $mockRepository = $this->sut->getRepository();

        $mockRepository->expects($this->once())
            ->method('find')
            ->with($id)
            ->will($this->returnValue($map));
        /** @var Repository $mockRepository */

        $this->sut->setRepository($mockRepository);

        $actual = $this->sut->getById($id);
        $this->assertSame($map, $actual);
    }

    public function testGetByDeskId()
    {
        $deskId = $this->getId();

        $map = new CompanyMapModel;
        $map->setDeskId($deskId);

        /** @var PHPUnit_Framework_MockObject_MockObject $mockRepository */
        $mockRepository = $this->sut->getRepository();

        $mockRepository->expects($this->once())
            ->method('findOneBy')
            ->with(array('deskId' => $deskId))
            ->will($this->returnValue($map));
        /** @var Repository $mockRepository */

        $this->sut->setRepository($mockRepository);

        $actual = $this->sut->getByDeskId($deskId);
        $this->assertSame($map, $actual);
    }

    public function testSetCompanyClientEventManagerSetsListeners()
    {
        /** @var \DeskModule\Client\Company\Event\Manager|PHPUnit_Framework_MockObject_MockObject  $companyClientEventManager */
        $companyClientEventManager =  $this->getMock('\DeskModule\Client\Company\Event\Manager');
        $companyClientEventManager->expects($this->at(0))->method('attachListener')
            ->with($this->isInstanceOf('\DeskModule\Client\Company\Event\Response\Listener\PostCreateExecuteMapCreate'));

        $this->sut->setCompanyClientEventManager($companyClientEventManager);
        $this->assertSame($companyClientEventManager, $this->sut->getCompanyClientEventManager());
    }

    protected function getMockRepository()
    {
        return $this->getMock('\DeskModule\Model\Map\Repository\Company', array(), array(), '', false);
    }


}