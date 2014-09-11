<?php
/**
 * Created by PhpStorm
 * @User joshuapacheco
 * @category WebPT
 * @package DeskModule
 * @copyright Copyright (c) 2014 WebPT, INC
 */

namespace tests\src\DeskModule\Queue\Marshal\Company;


use DeskModule\Queue\Marshal\Company\CompanyToJson;
use EMRCoreTest\Helper\Reflection;
use EMRModel\Company\Company;
use PHPUnit_Framework_TestCase;

/**
 * Class CompanyToJsonTest
 * @package tests\src\DeskModule\Queue\Marshal\Company
 * @group Marshaller
 */
class CompanyToJsonTest extends PHPUnit_Framework_TestCase {

    /** @var  Company */
    private $company;

    /** @var  CompanyToJson */
    private $marshaller;

    public function setUp() {
        parent::setUp();

        $this->company    = new Company();
        $this->marshaller = new CompanyToJson();
    }

    public function testMarshallValidatedItem() {
        $company = $this->getCompany()
            ->setId(1)
            ->setName('foo');

        $this->getMarshaller();

        $json = Reflection::invoke($this->getMarshaller(), 'marshallValidatedItem', array($company));
        $result = json_decode($json, true);
        $this->assertEquals($company->getId(), $result['id']);
        $this->assertEquals($company->getName(), $result['name']);
    }

    /**
     * @return Company
     */
    private function getCompany() {
        return $this->company;
    }

    /**
     * @return CompanyToJson
     */
    private function getMarshaller() {
        return $this->marshaller;
    }
}
 