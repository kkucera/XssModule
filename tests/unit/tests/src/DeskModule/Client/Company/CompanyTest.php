<?php
/**
 * 
 * 
 * @copyright Copyright (c) 2014 WebPT, INC
 */

namespace Unit\src\DeskModule\Client\Company;

use Desk\Relationship\Resource\EmbeddedCommand;
use Desk\Relationship\Resource\Model;
use DeskModule\Client\Company\Company;
use DeskModule\Client\Company\Event\Response\Create\PostExecute;
use DeskModule\Client\Company\Event\Response\Create\PreExecute;
use EMRCoreTest\Helper\Reflection;
use EMRModel\Company\Company as CompanyModel;
use Zend\EventManager\Event;

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

    protected function setUp()
    {
        parent::setUp();

        $this->sut = new Company;
        $this->sut->setCompanyClientEventManager($this->getMock('\DeskModule\Client\Company\Event\Manager'));
        $this->sut->setCompanyToCreateCommandMarshaller($this->getMock('\DeskModule\Client\Company\Marshaller\CompanyToCreateCommand'));
        $this->sut->setCompanyToUpdateCommandMarshaller($this->getMock('\DeskModule\Client\Company\Marshaller\CompanyToUpdateCommand'));
        $this->sut->setDeskIdToShowCommandMarshaller($this->getMock('\DeskModule\Client\Company\Marshaller\DeskIdToShowCommand'));
        $this->sut->setLogger($this->getMock('\Logger', array(), array(), '', false));
    }

    public function testGetListReturnsResourceModel() {
        $page = 1;
        $perPage = 50;

        $mockedClientCommand = $this->getMock('\DeskModule\Client\ClientCommand', array(), array(), '', false);
        $mockedClientCommand->expects($this->exactly(2))
            ->method('set')
            ->willReturnMap(array(
                array('page', $page),
                array('per_page', $perPage)
            ));
        $mockedClientCommand->expects($this->once())
            ->method('execute')
            ->willReturn($this->getMock('\Desk\Relationship\Resource\Model'));

        $mockedClientCommand->expects($this->once())
            ->method('prepareOperation')
            ->withAnyParameters()
            ->willReturn($mockedClientCommand);

        $mockedClientFactory = $this->getMock('\DeskModule\Client\Factory');
        $mockedClientFactory->expects($this->once())
            ->method('get')
            ->willReturn($mockedClientCommand);

        $this->sut->setClientFactory($mockedClientFactory);

        $response = $this->sut->getList($page, $perPage);
        $this->assertInstanceOf('\Desk\Relationship\Resource\Model', $response);
    }

    /**
     * @expectedException \DeskModule\Company\Exception\DeprecateFailed
     */
    public function testDeprecateThrowsExceptionIfResponseIdIsLessThanOne() {
        $deskCompany = new Model(array(
            '_links'    => 'foo',
            '_embedded' => 'bar'
        ));

        $responsePlugin = $this->getMock('stdClass', array('get'));
        $responsePlugin->expects($this->once())
            ->method('get')
            ->with('id')
            ->willReturn(0);

        $clientCommandPlugin = $this->getMock('stdClass', array('execute', 'getCommand'));
        $clientCommandPlugin->expects($this->once())
            ->method('execute')
            ->willReturn($responsePlugin);

        $mockedEmbeddedCommand = $this->getMock('\Desk\Relationship\Resource\EmbeddedCommand');
        $mockedEmbeddedCommand->expects($this->once())
            ->method('getName')
            ->willReturn('foobar');

        $clientCommandPlugin->expects($this->once())
            ->method('getCommand')
            ->willReturn($mockedEmbeddedCommand);

        $mockedMarshaller = $this->getMock('\DeskModule\Client\Company\Marshaller\CompanyToDeprecateCommand');
        $mockedMarshaller->expects($this->once())
            ->method('marshall')
            ->with($deskCompany)
            ->willReturn($clientCommandPlugin);

        $this->sut->setCompanyToDeprecateCommandMarshaller($mockedMarshaller);

        $this->sut->deprecate($deskCompany);
    }

    public function testTriggerSetsTarget()
    {
        $event = new Event;

        $this->assertNull($event->getTarget());

        Reflection::invoke($this->sut, 'trigger', array($event));

        $this->assertSame($this->sut, $event->getTarget());
    }

    public function testCreateTriggersEvents()
    {
        $company = new CompanyModel;

        $clientCommand = $this->getMock('\DeskModule\Client\ClientCommand', array(), array(), '', false);

        /** @var \PHPUnit_Framework_MockObject_MockObject $marshaller */
        $marshaller = $this->sut->getCompanyToCreateCommandMarshaller();
        $marshaller->expects($this->once())->method('marshall')
            ->with($company)->will($this->returnValue($clientCommand));

        /** @var \PHPUnit_Framework_MockObject_MockObject $eventManager */
        $eventManager = $this->sut->getCompanyClientEventManager();
        $eventManager->expects($this->at(0))->method('trigger')
            ->with($this->isInstanceOf('\DeskModule\Client\Company\Event\Response\Create\PreExecute'));

        $clientCommand->expects($this->once())->method('execute')
            ->will($this->returnValue($expected = $this->getMock('\Desk\Relationship\Resource\Model')));

        $eventManager->expects($this->at(1))->method('trigger')
            ->with($this->isInstanceOf('\DeskModule\Client\Company\Event\Response\Create\PostExecute'));

        $actual = $this->sut->create($company);
        $this->assertSame($expected, $actual);
    }

    public function testGetByDeskIdTriggersEvents()
    {
        $deskId = 1001;

        $clientCommand = $this->getMock('\DeskModule\Client\ClientCommand', array(), array(), '', false);

        /** @var \PHPUnit_Framework_MockObject_MockObject $marshaller */
        $marshaller = $this->sut->getDeskIdToShowCommandMarshaller();
        $marshaller->expects($this->once())->method('marshall')
            ->with($deskId)->will($this->returnValue($clientCommand));

        /** @var \PHPUnit_Framework_MockObject_MockObject $eventManager */
        $eventManager = $this->sut->getCompanyClientEventManager();
        $eventManager->expects($this->at(0))->method('trigger')
            ->with($this->isInstanceOf('\DeskModule\Client\Company\Event\Response\Read\PreExecute'));

        $clientCommand->expects($this->once())->method('execute')
            ->will($this->returnValue($expected = $this->getMock('\Desk\Relationship\Resource\Model')));

        $eventManager->expects($this->at(1))->method('trigger')
            ->with($this->isInstanceOf('\DeskModule\Client\Company\Event\Response\Read\PostExecute'));

        $actual = $this->sut->getByDeskId($deskId);
        $this->assertSame($expected, $actual);
    }

    public function testUpdateTriggersEvents()
    {
        $company = new CompanyModel;
        $deskId = 2001;

        $clientCommand = $this->getMock('\DeskModule\Client\ClientCommand', array(), array(), '', false);

        /** @var \PHPUnit_Framework_MockObject_MockObject $marshaller */
        $marshaller = $this->sut->getCompanyToUpdateCommandMarshaller();
        $marshaller->expects($this->once())->method('marshall')
            ->with($company)->will($this->returnValue($clientCommand));

        /** @var \PHPUnit_Framework_MockObject_MockObject $eventManager */
        $eventManager = $this->sut->getCompanyClientEventManager();
        $eventManager->expects($this->at(0))->method('trigger')
            ->with($this->isInstanceOf('\DeskModule\Client\Company\Event\Response\Update\PreExecute'));

        $clientCommand->expects($this->once())->method('execute')
            ->will($this->returnValue($expected = $this->getMock('\Desk\Relationship\Resource\Model')));

        $eventManager->expects($this->at(1))->method('trigger')
            ->with($this->isInstanceOf('\DeskModule\Client\Company\Event\Response\Update\PostExecute'));

        $actual = $this->sut->update($company, $deskId);
        $this->assertSame($expected, $actual);
    }
} 