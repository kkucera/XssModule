<?php
/**
 * @copyright Copyright (c) 2014 WebPT, INC
 */

namespace XssModule\Controller;

use EMRCore\Zend\Mvc\Controller\ActionControllerAbstract;
use Zend\View\Model\ViewModel;

class IndexController extends ActionControllerAbstract
{

    public function indexAction()
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        return $viewModel;
    }

    public function filterAction()
    {
        /** @var \Zend\Http\PhpEnvironment\Request $request */
        $request = $this->getRequest();
        $testInput = $request->getPost('testInput');

        $viewModel = new ViewModel();
        $viewModel->setVariable('testInput', $testInput);
        $viewModel->setTerminal(true);
        return $viewModel;
    }

} 