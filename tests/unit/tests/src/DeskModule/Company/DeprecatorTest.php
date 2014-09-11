<?php
/**
 *
 *
 * @copyright Copyright (c) 2014 WebPT, INC
 */

namespace Unit\src\DeskModule\Company;

use Desk\Relationship\Resource\Model;
use DeskModule\Client\Company\Company;
use DeskModule\Client\Company\Deprecator;
use DeskModule\Model\Map\Company as MapModel;
use EMRModel\Company\Company as CompanyModel;

/**
 *
 *
 * @category WebPT
 * @package
 */
class DeprecatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var  Deprecator */
    private $sut;

    protected function setUp()
    {
        parent::setUp();

        $this->sut = new Deprecator;
    }

    public function testDeprecateAllCompanies()
    {
        /** @var Company|\PHPUnit_Framework_MockObject_MockObject $clientService */
        $clientService = $this->getMock('DeskModule\Client\Company\Company');

        $companyListPage = $this->getMock('\Desk\Relationship\Resource\Model');

        $clientService->expects($this->once())
            ->method('getList')
            ->will($this->returnValue($companyListPage));


        $deprecatedDeskCompany = new Model;
        $deprecatedDeskCompany->set('custom_fields', array('deprecated' => true));

        $deskCompany = new Model;
        $deskCompany->set('custom_fields', array('deprecated' => null));

        $companyListPage->expects($this->once())
            ->method('getEmbedded')
            ->with('entries')
            ->will($this->returnValue(array($deprecatedDeskCompany, $deskCompany)));

        $eventManager = $this->getMock('DeskModule\Client\Company\Event\Manager');

        $this->sut->setCompanyClientService($clientService);
        $this->sut->setCompanyClientEventManager($eventManager);
        $this->sut->setLogger($this->getMock('\Logger', array(), array(), '', false));

        $response = $this->sut->deprecateAllCompanies();

        $this->assertTrue($response);

    }

}