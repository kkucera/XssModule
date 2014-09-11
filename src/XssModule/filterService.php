<?php
/**
 * @copyright Copyright (c) 2014 WebPT, INC
 */

namespace XssModule;

use Zend\Mvc\MvcEvent;

class filterService
{

    /**
     * @param MvcEvent $event
     */
    public function filterInput(MvcEvent $event)
    {
        $request = $event->getRequest();
    }

} 