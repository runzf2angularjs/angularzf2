<?php
/**
 * Copyright (C) 2015 Orange
 *
 * This software is confidential and proprietary information of Orange.
 * You shall not disclose such Confidential Information and shall use it only
 * in accordance with the terms of the agreement you entered into.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * If you are Orange employee you shall use this software in accordance with
 * the Orange Source Charter (http://opensource.itn.ftgroup/index.php/Orange_Source).
 */

namespace Oft\Widget;

use Oft\Mvc\Application;
use Zend\View\Model\ModelInterface;
use Zend\View\Model\ViewModel;

abstract class WidgetAbstract
{
    /** @var Application */
    protected $app;

    public function __construct(WidgetFactory $widgetFactory)
    {
        $this->app = $widgetFactory->getApp();
    }

    protected function render($template, $model)
    {
        if ($model instanceof ModelInterface) {
            $viewModel = $model;
        } else {
            $viewModel = new ViewModel;
            $viewModel->setVariables($model, true);
        }

        $viewModel->setTemplate($template);

        return $this->app->view->render($viewModel);
    }

    abstract public function __invoke();
}
