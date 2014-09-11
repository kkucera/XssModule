<?php
/**
 * 
 * 
 * @copyright Copyright (c) 2014 WebPT, INC
 */

namespace Unit\src\DeskModule\Client\Customer\Response;

use Desk\Relationship\Resource\EmbeddedCommand;
use Desk\Relationship\Resource\Model;
use DeskModule\Client\ClientCommand;
use DeskModule\Client\Customer\Event\Response\Create\PostExecute;
use DeskModule\Client\Customer\Event\Response\Create\PreExecute;
use DeskModule\Client\Customer\Response\Validator;

/**
 *
 *
 * @category WebPT
 * @package
 */
class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var  Validator */
    private $sut;

    protected function setUp()
    {
        parent::setUp();

        $this->sut = new Validator;
        $this->sut->setCustomerClientEventManager($this->getMock('\DeskModule\Client\Customer\Event\Manager'));
        $this->sut->setLogger($this->getMock('\Logger', array(), array(), '', false));
    }

    public function testBindListeners()
    {
        $eventManager = $this->getMock('\DeskModule\Client\Customer\Event\Manager');

        $eventManager->expects($this->at(0))->method('attachListener')
            ->with($this->isInstanceOf('\DeskModule\Client\Customer\Event\Response\Listener\PostCreateExecuteValidator'));

        $eventManager->expects($this->at(1))->method('attachListener')
            ->with($this->isInstanceOf('\DeskModule\Client\Customer\Event\Response\Listener\PostReadExecuteValidator'));

        $eventManager->expects($this->at(2))->method('attachListener')
            ->with($this->isInstanceOf('\DeskModule\Client\Customer\Event\Response\Listener\PostUpdateExecuteValidator'));

        $this->sut->setCustomerClientEventManager($eventManager);
    }

    public function testValidateResponseValid()
    {
        $clientCommand = new ClientCommand(
            $this->getMock('\Desk\Client'),
            $this->getMock('\DeskModule\Transaction\Transaction'),
            $this->getMock('\DeskModule\Model\Transaction\Marshaller\Guzzle\Http\Message\RequestInterfaceToTransaction')
        );
        $clientCommand->setCommand(new EmbeddedCommand());
        $preExecute = new PreExecute($clientCommand);
        $response = new Model;
        $response->set('id', 1);
        $postExecute = new PostExecute($preExecute, $response);

        $this->assertFalse($postExecute->propagationIsStopped());

        $actual = $this->sut->validateResponse($postExecute);
        $this->assertFalse($postExecute->propagationIsStopped());
        $this->assertTrue($actual);
    }

    public function testValidateResponseInvalid()
    {
        $clientCommand = new ClientCommand(
            $this->getMock('\Desk\Client'),
            $this->getMock('\DeskModule\Transaction\Transaction'),
            $this->getMock('\DeskModule\Model\Transaction\Marshaller\Guzzle\Http\Message\RequestInterfaceToTransaction')
        );
        $clientCommand->setCommand(new EmbeddedCommand());
        $preExecute = new PreExecute($clientCommand);
        $response = new Model;
        $response->set('id', 0);
        $postExecute = new PostExecute($preExecute, $response);

        $this->assertFalse($postExecute->propagationIsStopped());

        $actual = $this->sut->validateResponse($postExecute);
        $this->assertTrue($postExecute->propagationIsStopped());
        $this->assertFalse($actual);
    }
} 