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

namespace Oft\Service\Provider;

use Oft\Mvc\Application;
use Oft\Service\FactoryInterface;
use Oft\Service\ServiceLocatorInterface;
use Oft\View\Resolver\ModuleResolver;
use Oft\View\View as Oft_View;

class View implements FactoryInterface
{

    /** @var Application */
    protected $app;

    /**
     * Instancie et configure la vue
     *
     * @param Application $app
     * @return Oft_View
     */
    public function create(ServiceLocatorInterface $app)
    {
        $view = new Oft_View();
        $view->setApplication($app)
            ->setBaseUrl($app->http->request->getBaseUrl())
            ->setResolver(new ModuleResolver($app->moduleManager))
            ->setHelperPluginManager($app->get('ViewHelperPlugins'));

        return $view;
    }

}
