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
use DeskModule\Queue\Marshal\Customer\JsonToCustomerDto;
use EMRCoreTest\Helper\Reflection;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use Zend\Stdlib\Hydrator\Reflection as ReflectionHydrator;

/**
 * Class JsonToCompanyTest
 * @package tests\src\DeskModule\Queue\Marshal\Customer
 * @group Marshaller
 */
class JsonToCustomerDtoTest extends PHPUnit_Framework_TestCase
{

    /** @var  CustomerDto */
    private $customerDto;

    /** @var  JsonToCustomerDto */
    private $marshaller;

    public function setUp() {
        parent::setUp();

        $this->customerDto = new CustomerDto();
        $this->marshaller = new JsonToCustomerDto();
        $this->marshaller->setReflectionHydrator(new ReflectionHydrator);
    }

    public function testMarshall() {
        $customerDto = $this->getCustomerDto()
            ->setWebptId(54)
            ->setDeskId('desk_432')
            ->setDeskCompanyId(3)
            ->setEmail('my@meme.com')
            ->setFirstName('moly')
            ->setLastName('golly')
            ->setUserType('princess');

        $customerDtoArray = array(
            'webptId'   => $customerDto->getWebptId(),
            'deskId' => $customerDto->getDeskId(),
            'deskCompanyId' => $customerDto->getDeskCompanyId(),
            'email' => $customerDto->getEmail(),
            'firstName' => $customerDto->getFirstName(),
            'lastName' => $customerDto->getLastName(),
            'userType' => $customerDto->getUserType()
        );

        $json = json_encode($customerDtoArray);

        $result = $this->getMarshaller()->marshall($json);

        $this->assertEquals($customerDto, $result);
    }

//    /**
//     * @expectedException InvalidArgumentException
//     */
//    public function testMarshallThrowsExceptionUponParameterNonString() {
//        $this->getMarshaller()->marshall(array('stuff'=>'things'));
//    }
//
//    /**
//     * @expectedException InvalidArgumentException
//     */
//    public function testMarshallThrowsExceptionUponParameterEmptyString() {
//        $this->getMarshaller()->marshall('');
//    }
//
//    /**
//     * @expectedException InvalidArgumentException
//     */
//    public function testMarshallThrowsExceptionUponJsonError() {
//        $this->getMarshaller()->marshall('goblygook');
//    }

    /**
     * @return CustomerDto
     */
    private function getCustomerDto() {
        return $this->customerDto;
    }

    /**
     * @return JsonToCustomerDto
     */
    private function getMarshaller() {
        return $this->marshaller;
    }
}
 