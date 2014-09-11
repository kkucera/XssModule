<?php
/**
 * 
 * 
 * @copyright Copyright (c) 2014 WebPT, INC
 */

namespace Unit\src\DeskModule\Event\Listener;

use DeskModule\Event\Listener\Listener;

/**
 *
 *
 * @category WebPT
 * @package
 */
class ListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNotCallable()
    {
        new Listener('asdf', 'qwer', 'zxcv');
    }
} 