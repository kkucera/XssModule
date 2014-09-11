<?php
/**
 *
 *
 * @copyright Copyright (c) 2014 WebPT, INC
 */

namespace Unit\src\DeskModule\Map;

use DeskModule\Map\Customer as CustomerMapService;
use DeskModule\Model\Map\Customer as CustomerMapModel;
use DeskModule\Model\Map\Repository\Customer as Repository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use EMRCore\DoctrineConnector\Adapter\Adapter;
use PHPUnit_Framework_MockObject_MockObject;

/**
 *
 *
 * @category WebPT
 * @package
 * @group Map
 */
class CustomerTest extends \PHPUnit_Framework_TestCase
{

    /** @var  CustomerMapService */

    private $sut;

    public function setUp()
    {
        parent::setUp();
        $this->sut = new CustomerMapService();
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

        $this->sut->create(new CustomerMapModel());
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

        $this->sut->update(new CustomerMapModel());
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

        $this->sut->delete(new CustomerMapModel());
    }

    public function testGetById()
    {
        $id = $this->getId();
        $map = new CustomerMapModel;

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

        $map = new CustomerMapModel;
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

    public function testSetCustomerClientEventManagerSetsListeners()
    {
        /** @var \DeskModule\Client\Customer\Event\Manager|PHPUnit_Framework_MockObject_MockObject  $companyClientEventManager */
        $companyClientEventManager =  $this->getMock('\DeskModule\Client\Customer\Event\Manager');
        $companyClientEventManager->expects($this->at(0))->method('attachListener')
            ->with($this->isInstanceOf('\DeskModule\Client\Customer\Event\Response\Listener\PostCreateExecuteMapCreate'));

        $this->sut->setCustomerClientEventManager($companyClientEventManager);
        $this->assertSame($companyClientEventManager, $this->sut->getCustomerClientEventManager());
    }

    protected function getMockRepository()
    {
        return $this->getMock('\DeskModule\Model\Map\Repository\Customer', array(), array(), '', false);
    }

}