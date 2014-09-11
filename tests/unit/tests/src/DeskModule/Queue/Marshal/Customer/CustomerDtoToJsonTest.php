<?php
/**
 * Created by PhpStorm
 * @User joshuapacheco
 * @category WebPT
 * @package DeskModule
 * @copyright Copyright (c) 2014 WebPT, INC
 */

namespace tests\src\DeskModule\Queue\Marshal\Customer;


use DeskModule\Customer\CustomerDto;
use DeskModule\Queue\Marshal\Customer\CustomerDtoToJson;
use EMRCoreTest\Helper\Reflection;
use PHPUnit_Framework_TestCase;
use Zend\Stdlib\Hydrator\Reflection as ReflectionHydrator;

/**
 * Class CompanyToJsonTest
 * @package tests\src\DeskModule\Queue\Marshal\Customer
 * @group Marshaller
 */
class CustomerDtoToJsonTest extends PHPUnit_Framework_TestCase
{

    /** @var  CustomerDto */
    private $customerDto;

    /** @var  CustomerDtoToJson */
    private $marshaller;

    public function setUp() {
        parent::setUp();

        $this->customerDto = new CustomerDto();
        $this->marshaller = new CustomerDtoToJson();
        $this->marshaller->setReflectionHydrator(new ReflectionHydrator);
    }

    public function testMarshallValidatedItem() {
        $customerDto = $this->getCustomerDto()
            ->setWebptId(1)
            ->setDeskId('desk_1')
            ->setEmail('me@mine.com')
            ->setDeskCompanyId(23)
            ->setFirstName('mr')
            ->setLastName('anderson')
            ->setUserType('guyish');

        $this->getMarshaller();

        $json = Reflection::invoke($this->getMarshaller(), 'marshallValidatedItem', array($customerDto));
        $result = json_decode($json, true);
        $this->assertEquals($customerDto->getWebptId(), $result['webptId']);
        $this->assertEquals($customerDto->getDeskId(), $result['deskId']);
        $this->assertEquals($customerDto->getEmail(), $result['email']);
        $this->assertEquals($customerDto->getDeskCompanyId(), $result['deskCompanyId']);
        $this->assertEquals($customerDto->getFirstName(), $result['firstName']);
        $this->assertEquals($customerDto->getLastName(), $result['lastName']);
        $this->assertEquals($customerDto->getUserType(), $result['userType']);

    }

    /**
     * @return CustomerDto
     */
    private function getCustomerDto() {
        return $this->customerDto;
    }

    /**
     * @return CustomerDtoToJson
     */
    private function getMarshaller() {
        return $this->marshaller;
    }
}
 