<?php
/**
 * Created by PhpStorm
 * @User joshuapacheco
 * @category WebPT
 * @package DeskModule
 * @copyright Copyright (c) 2014 WebPT, INC
 */

namespace Unit\src\DeskModule\Model\Map;

use DeskModule\Model\Map\Customer;
use PHPUnit_Framework_TestCase;

class CustomerTest extends PHPUnit_Framework_TestCase {

    /** @var  Customer */
    private $model;

    /** @var  int */
    private static $timestamp;

    /** @var  string */
    private static $dateString;

    public function setUp() {
        parent::setUp();

        $this->model = new Customer();
        self::$timestamp = strtotime('now');
        self::$dateString = date('Y-m-d', self::$timestamp);
    }

    public function testSetCreated() {
        $model = $this->getModel()->setCreated(self::$dateString);

        $this->assertEquals(self::$dateString, $model->getCreated()->format('Y-m-d'));
    }

    public function testGetCreated() {
        $this->getModel()->setCreated(self::$dateString);

        $result = $this->getModel()->getCreated('Y');

        $this->assertEquals(date('Y', self::$timestamp), $result);
    }

    public function testSetUpdated() {
        $model = $this->getModel()->setUpdated(self::$dateString);

        $this->assertEquals(self::$dateString, $model->getUpdated()->format('Y-m-d'));
    }

    public function testGetUpdated() {
        $this->getModel()->setUpdated(self::$dateString);

        $result = $this->getModel()->getUpdated('Y');

        $this->assertEquals(date('Y', self::$timestamp), $result);
    }

    /**
     * @return Customer
     */
    private function getModel() {
        return $this->model;
    }
}
 