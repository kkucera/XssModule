<?php
/**
 * 
 * 
 * @copyright Copyright (c) 2014 WebPT, INC
 */

namespace Unit\src\DeskModule\Config;

use DeskModule\Config\Config;
use Zend\Config\Config as ZendConfig;

/**
 *
 *
 * @category WebPT
 * @package
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /** @var  Config */
    private $sut;

    protected function setUp()
    {
        parent::setUp();

        $this->sut = new Config;
    }

    public function testDeskConfig()
    {
        $this->sut->setConfiguration(new ZendConfig(array(
            Config::KEY_DESK => $expected = new \stdClass,
        )));

        $actual = $this->sut->getDeskConfig();

        $this->assertSame($expected, $actual);
    }

    public function testClientConfig()
    {
        $this->sut->setConfiguration(new ZendConfig(array(
            Config::KEY_DESK => array(
                Config::KEY_CLIENT => $expected = new \stdClass,
            ),
        )));

        $actual = $this->sut->getClientConfig();

        $this->assertSame($expected, $actual);
    }
} 