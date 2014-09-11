<?php
/**
 * Created by PhpStorm
 * @User joshuapacheco
 * @category WebPT
 * @package DeskModule
 * @copyright Copyright (c) 2014 WebPT, INC
 */

namespace tests\src\DeskModule\Queue\Marshal\Company;


use DeskModule\Queue\Marshal\Company\JsonToCompany;
use EMRModel\Company\Company;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use Zend\Stdlib\Hydrator\Reflection as ReflectionHydrator;

/**
 * Class JsonToCompanyTest
 * @package tests\src\DeskModule\Queue\Marshal\Company
 * @group Marshaller
 */
class JsonToCompanyTest extends PHPUnit_Framework_TestCase {

    /** @var  Company */
    private $company;

    /** @var  JsonToCompany */
    private $marshaller;

    public function setUp() {
        parent::setUp();

        $this->company    = new Company();
        $this->marshaller = new JsonToCompany();
        $this->marshaller->setReflectionHydrator(new ReflectionHydrator());
    }

    public function testMarshall() {
        $companyObj = $this->getCompany()
            ->setId(1)
            ->setName('foo');

        $companyArray = array(
            'id'   => $companyObj->getId(),
            'name' => $companyObj->getName()
        );

        $json = json_encode($companyArray);

        $result = $this->getMarshaller()->marshall($json);

        $this->assertEquals($companyObj, $result);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMarshallThrowsExceptionUponParameterNonString() {
        $this->getMarshaller()->marshall(1);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMarshallThrowsExceptionUponParameterEmptyString() {
        $this->getMarshaller()->marshall('');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMarshallThrowsExceptionUponJsonError() {
        $this->getMarshaller()->marshall('goblygook');
    }

    /**
     * @return Company
     */
    private function getCompany() {
        return $this->company;
    }

    /**
     * @return JsonToCompany
     */
    private function getMarshaller() {
        return $this->marshaller;
    }
}
 