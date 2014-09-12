<?php
/**
 * @copyright Copyright (c) 2014 WebPT, INC
 */

namespace XssModule;

use Zend\Mvc\MvcEvent;
use Zend\Stdlib\Parameters;
use Zend\Mvc\Router\Http\RouteMatch;

class filterService
{

    /**
     * @param MvcEvent $event
     */
    public function filterInput(MvcEvent $event)
    {
        $request = $event->getRequest();
        if($request instanceof \Zend\Http\PhpEnvironment\Request){
            if($this->isRouteExcluded($event->getRouteMatch())){
                return;
            }
            $postArray = $request->getPost()->toArray();
            $filtered = array();
            foreach($postArray as $name=>$value){
                $filtered[$name] = $this->filter($value);
            }
            $request->setPost(new Parameters($filtered));
        }
    }

    private function isRouteExcluded(RouteMatch $routeMatch)
    {
        $controller = $routeMatch->getParam('controller');
        $action = $routeMatch->getParam('action');
        // todo: evaluate if controller / action are excluded
        return false;
    }

    /**
     * @param $value
     * @return mixed
     */
    private function filter($value)
    {
        return str_replace(' ', '', $value);
    }

} 