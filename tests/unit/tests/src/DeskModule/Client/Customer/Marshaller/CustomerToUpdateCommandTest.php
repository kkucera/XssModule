<?php
/**
 * @copyright Copyright (c) 2014 WebPT, INC
 */

namespace Unit\src\DeskModule\Client\Customer\Marshaller;

use Desk\Relationship\Resource\EmbeddedCommand;
use DeskModule\Client\Customer\Marshaller\CustomerToUpdateCommand;
use DeskModule\Customer\CustomerDto;
use EMRCoreTest\Helper\Reflection;

class CustomerToUpdateCommandTest extends \PHPUnit_Framework_TestCase
{

    /** @var CustomerToUpdateCommand */
    private $marshaller;

    public function setUp()
    {
        $this->marshaller = new CustomerToUpdateCommand;
    }

    /**
     * @expectedException \EMRCore\Marshaller\Exception\CannotMarshall
     */
    public function testThrowsInvalidArgument()
    {
        $this->marshaller->marshall(new \stdClass());
    }

    public function testMarshall()
    {
        $customerDto = new CustomerDto;
        $customerDto->setDeskId(1111111);
        $customerDto->setWebptId(232);
        $customerDto->setFirstName('Imma');
        $customerDto->setLastName('Tool');
        $customerDto->setEmail('ImmaTool@lala.com');
        $customerDto->setDeskCompanyId(99);
        $customerDto->setUserType('short');

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
        $result = $this->marshaller->marshall($customerDto);
        $this->assertInstanceOf('\DeskModule\Client\ClientCommand', $result);

        $command = $clientCommand->getCommand();
        $this->assertEquals($customerDto->getDeskId(), $command->get('id'));
        $this->assertEquals($customerDto->getWebptId(), $command->get('external_id'));
        $this->assertEquals($customerDto->getFirstName(), $command->get('first_name'));
        $this->assertEquals($customerDto->getLastName(), $command->get('last_name'));
        $this->assertEquals($customerDto->getDeskCompanyId(), $command->get('company_id'));
        $emails = $command->get('emails');
        $this->assertCount(1,$emails);
        $this->assertEquals($customerDto->getEmail(), $emails[0]->value);
        $customFields = $command->get('custom_fields');
        $this->assertEquals($customerDto->getUserType(), $customFields['user_type']);
    }

} 