<?php
/**
 * 
 * 
 * @copyright Copyright (c) 2014 WebPT, INC
 */

namespace Unit\src\DeskModule\Company;

use Desk\Relationship\Resource\Model;
use DeskModule\Company\Company;
use DeskModule\Model\Map\Company as MapModel;
use EMRModel\Company\Company as CompanyModel;
use PHPUnit_Framework_MockObject_MockObject;

/**
 *
 *
 * @category WebPT
 * @package
 */
class CompanyTest extends \PHPUnit_Framework_TestCase
{
    /** @var  Company */
    private $sut;

    /** @var  CompanyModel */
    private static $companyModel;

    /** @var  MapModel */
    private static $mapModel;

    protected function setUp()
    {
        parent::setUp();

        $this->sut = new Company;
        $this->sut->setCompanyMapService($this->getMock('\DeskModule\Map\Company'));
        $this->sut->setCompanyClientService($this->getMock('\DeskModule\Client\Company\Company'));

        self::$companyModel = new CompanyModel;
        self::$mapModel = new MapModel();
    }

    /**
     * @expectedException \DeskModule\Company\Exception\NotFound
     */
    public function testNotStoreDueToNotPersisted()
    {
        $this->sut->store(self::$companyModel);
    }

    public function testCreate()
    {
        self::$companyModel->setId($companyId = 1001);

        /** @var \PHPUnit_Framework_MockObject_MockObject $mapService */
        $mapService = $this->sut->getCompanyMapService();
        $mapService->expects($this->once())->method('getByWebptId')
            ->with($companyId)->will($this->returnValue(null));

        /** @var \PHPUnit_Framework_MockObject_MockObject $clientService */
        $clientService = $this->sut->getCompanyClientService();
        $clientService->expects($this->once())->method('create')
            ->with(self::$companyModel);

        $this->sut->store(self::$companyModel);
    }

    public function testUpdate()
    {
        self::$companyModel->setId($companyId = 2001);

        /** @var \PHPUnit_Framework_MockObject_MockObject $mapService */
        $mapService = $this->sut->getCompanyMapService();
        $mapService->expects($this->once())->method('getByWebptId')
            ->with($companyId)->will($this->returnValue(self::$mapModel));

        /** @var \PHPUnit_Framework_MockObject_MockObject $clientService */
        $clientService = $this->sut->getCompanyClientService();
        $clientService->expects($this->once())->method('update')
            ->with(self::$companyModel);

        $this->sut->store(self::$companyModel);
    }

    public function testDeprecateReturnsInstanceOfRelationshipResourceModel() {
        $mapModel = self::$mapModel->setDeskId(1);
        $deskModel = new Model();

        /** @var Company|PHPUnit_Framework_MockObject_MockObject $sut */
        $sut = $this->getMock('DeskModule\Company\Company', array('getMapByCompany', 'getCompanyClientService'));
        $sut->expects($this->once())
            ->method('getMapByCompany')
            ->with(self::$companyModel)
            ->willReturn($mapModel);

        $clientServiceMock = $this->getMock('DeskModule\Client\Company\Company');
        $clientServiceMock->expects($this->once())
            ->method('getByDeskId')
            ->with($mapModel->getDeskId())
            ->willReturn($deskModel);
        $clientServiceMock->expects($this->once())
            ->method('deprecate')
            ->with($deskModel)
            ->willReturn($deskModel);

        $sut->expects($this->exactly(2))
            ->method('getCompanyClientService')
            ->willReturn($clientServiceMock);

        $result = $sut->deprecate(self::$companyModel);

        $this->assertInstanceOf('Desk\Relationship\Resource\Model', $result);
    }

    public function testDeprecateReturnsFalseIfNoCompanyMapExists() {
        $mapModel = self::$mapModel->setDeskId(1);
        $deskModel = new Model();

        /** @var Company|PHPUnit_Framework_MockObject_MockObject $sut */
        $sut = $this->getMock('DeskModule\Company\Company', array('getMapByCompany'));
        $sut->expects($this->once())
            ->method('getMapByCompany')
            ->with(self::$companyModel);

        $result = $sut->deprecate(self::$companyModel);

        $this->assertFalse($result);
    }

    public function testDeprecateReturnsFalseIfNoDeskModelExists() {
        $mapModel = self::$mapModel->setDeskId(1);
        $deskModel = new Model();

        /** @var Company|PHPUnit_Framework_MockObject_MockObject $sut */
        $sut = $this->getMock('DeskModule\Company\Company', array('getMapByCompany', 'getCompanyClientService'));
        $sut->expects($this->once())
            ->method('getMapByCompany')
            ->with(self::$companyModel)
            ->willReturn($mapModel);

        $clientServiceMock = $this->getMock('DeskModule\Client\Company\Company');
        $clientServiceMock->expects($this->once())
            ->method('getByDeskId')
            ->with($mapModel->getDeskId());

        $sut->expects($this->once())
            ->method('getCompanyClientService')
            ->willReturn($clientServiceMock);

        $result = $sut->deprecate(self::$companyModel);

        $this->assertFalse($result);
    }
} 