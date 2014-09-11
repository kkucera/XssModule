<?php
/**
 * @copyright Copyright (c) 2014 WebPT, INC
 */

namespace Unit\src\DeskModule\Client;


use DeskModule\Client\ClientCommand;
use DeskModule\Model\Transaction\Transaction;
use EMRCoreTest\Helper\Reflection;
use Guzzle\Service\Command\OperationCommand;

class ClientCommandTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ClientCommand
     */
    private $clientCommand;

    public function setUp()
    {
        $guzzleClient = $this->getMock('\Desk\Client');
        $transactionService = $this->getMock('\DeskModule\Transaction\Transaction');
        $marshaller = $this->getMock('\DeskModule\Model\Transaction\Marshaller\Guzzle\Http\Message\RequestInterfaceToTransaction');
        $clientCommand = new ClientCommand($guzzleClient, $transactionService, $marshaller);

        $this->clientCommand = $clientCommand;
    }

    public function testConstructInitializesDependencies()
    {
        $guzzleClient = $this->getMock('\Desk\Client');
        $transactionService = $this->getMock('\DeskModule\Transaction\Transaction');
        $marshaller = $this->getMock('\DeskModule\Model\Transaction\Marshaller\Guzzle\Http\Message\RequestInterfaceToTransaction');
        $clientCommand = new ClientCommand($guzzleClient, $transactionService, $marshaller);

        $this->assertEquals($guzzleClient, $clientCommand->getGuzzleClient());
        $this->assertEquals($transactionService, $clientCommand->getTransactionService());
        $this->assertEquals($marshaller, $clientCommand->getRequestInterfaceToTransactionMarshaller());
    }

    public function testBeforeSendCreatesTransactionWithRequest()
    {
        $request = 'request';

        $command = $this->getMock('\Guzzle\Service\Command\OperationCommand');
        $command->expects($this->once())->method('getRequest')->will($this->returnValue($request));

        $event = $this->getMock('\Guzzle\Common\Event');
        $event->expects($this->once())
            ->method('offsetGet')
            ->with('command')
            ->will($this->returnValue($command));

        $transaction = new Transaction();

        /** @var \PHPUnit_Framework_MockObject_MockObject $marshaller */
        $marshaller = $this->clientCommand->getRequestInterfaceToTransactionMarshaller();
        $marshaller->expects($this->once())->method('marshall')->with($request)->will($this->returnValue($transaction));

        /** @var \PHPUnit_Framework_MockObject_MockObject $transactionService */
        $transactionService = $this->clientCommand->getTransactionService();
        $transactionService->expects($this->once())->method('create')->with($transaction);

        $this->clientCommand
            ->beforeSend($event, null, $this->getMock('\Symfony\Component\EventDispatcher\EventDispatcherInterface'));
    }

    public function testAfterSendUpdatesTransactionWithResponse()
    {
        $response = 'response';

        $command = $this->getMock('\Guzzle\Service\Command\OperationCommand');
        $command->expects($this->once())->method('getResponse')->will($this->returnValue($response));

        $event = $this->getMock('\Guzzle\Common\Event');
        $event->expects($this->once())
            ->method('offsetGet')
            ->with('command')
            ->will($this->returnValue($command));

        $transaction = new Transaction();
        $this->clientCommand->setTransaction($transaction);

        /** @var \PHPUnit_Framework_MockObject_MockObject $transactionService */
        $transactionService = $this->clientCommand->getTransactionService();
        $transactionService->expects($this->once())->method('update')->with($transaction);

        $this->clientCommand
            ->afterSend($event, null, $this->getMock('\Symfony\Component\EventDispatcher\EventDispatcherInterface'));
        $this->assertEquals($response, $transaction->getResponse());
    }

    public function testPrepareOperationSetsGuzzleCommand()
    {
        $operation = 'do stuff';
        $command = new \Desk\Relationship\Resource\EmbeddedCommand;

        /** @var \PHPUnit_Framework_MockObject_MockObject $guzzleClient */
        $guzzleClient = $this->clientCommand->getGuzzleClient();
        $guzzleClient->expects($this->once())
            ->method('getCommand')
            ->with($operation)
            ->will($this->returnValue($command));

        $this->clientCommand->prepareOperation($operation);

        $this->assertEquals($command, $this->clientCommand->getCommand());

    }



} 