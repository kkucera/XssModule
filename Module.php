<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace XssModule;

use EMRCore\Zend\Module\Console\ModuleEventAbstract;
use Zend\Console\Adapter\AdapterInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

/**
 *
 *
 * @category WebPT
 * @package
 */
class Module
{
    /**
     * @return mixed
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * @param MvcEvent $event
     */
    public function onBootstrap(MvcEvent $event)
    {
        //$serviceLocator = $event->getApplication()->getServiceManager();
        $filterService = new \XssModule\filterService();
        $em = $event->getApplication()->getEventManager();
        $em->attach(MvcEvent::EVENT_ROUTE, array($filterService, 'filterInput'));
    }

}
