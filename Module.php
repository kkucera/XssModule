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
class Module extends ModuleEventAbstract implements ConsoleUsageProviderInterface
{
    /**
     * @return mixed
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Returns an array or a string containing usage information for this module's Console commands.
     * The method is called with active Zend\Console\Adapter\AdapterInterface that can be used to directly access
     * Console and send output.
     *
     * If the result is a string it will be shown directly in the console window.
     * If the result is an array, its contents will be formatted to console window width. The array must
     * have the following format:
     *
     *     return array(
     *                'Usage information line that should be shown as-is',
     *                'Another line of usage info',
     *
     *                '--parameter'        =>   'A short description of that parameter',
     *                '-another-parameter' =>   'A short description of another parameter',
     *                ...
     *            )
     *
     * @param AdapterInterface $console
     * @return array|string|null
     */
    public function getConsoleUsage(AdapterInterface $console)
    {

    }

    /**
     * @param MvcEvent $event
     */
    public function onBootstrap(MvcEvent $event)
    {
        $em = $event->getApplication()->getEventManager();
        $filterService = $this->serviceLocator->get('\XssModule\filterService');
        $em->attach(MvcEvent::EVENT_DISPATCH, array($filterService, 'filterInput'));
    }

    /**
     * Each slice module should override this method to determine if controllers should expect
     * companyId being provided. Slices like SSO/Delegator don't need companyId specified as they have their own
     * schema that is not related to tenants.
     * @return bool
     */
    protected function checkForCompanyIdParam()
    {
        return false;
    }
}
