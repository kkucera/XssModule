<?php
/**
 * @copyright Copyright (c) 2014 WebPT, INC
 */

namespace Unit\src\DeskModule\Client\Customer\Marshaller;

use Desk\Relationship\Resource\EmbeddedCommand;
use DeskModule\Client\Customer\Marshaller\DeskIdToShowCommand;
use EMRCoreTest\Helper\Reflection;

class DeskIdToShowCommandTest extends \PHPUnit_Framework_TestCase
{

    /** @var DeskIdToShowCommand */
    private $marshaller;

    public function setUp()
    {
        $this->marshaller = new DeskIdToShowCommand;
    }

    /**
     * @expectedException \EMRCore\Marshaller\Exception\CannotMarshallPrimitive
     */
    public function testThrowsInvalidArgument()
    {
        $this->marshaller->marshall(1234);
    }

    public function testMarshall()
    {
        $id = '432';


        /** @var \PHPUnit_Framework_MockObject_MockObject|\DeskModule\Client\ClientCommand $clientCommand */
        $clientCommand = $this->getMock('\DeskModule\Client\ClientCommand', array('prepareOperation'), array(), '', false);
        $clientCommand->setCommand(new EmbeddedCommand());
        $clientCommand->expects($this->once())
            ->method('prepareOperation')
            ->with(Reflection::invoke($this->marshaller,'getOperation'))
            ->will($this->returnValue($clientCommand));

        /** @var \PHPUnit_Framework_MockObject_MockObject $factory */
        $factory = $this->getMock('DeskModule\Client\Factory');
        $factory->expects($this->once())
            ->method('get')
            ->will($this->returnValue($clientCommand));

        $this->marshaller->setClientFactory($factory);

        /** @var \DeskModule\Client\ClientCommand $result */
        $result = $this->marshaller->marshall($id);
        $this->assertInstanceOf('DeskModule\Client\ClientCommand', $result);

        $command = $result->getCommand();
        $this->assertEquals($id, $command->get('id'));
    }

} 