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

use Oft\Mvc\Context\RenderOptionsContext as MvcRenderOptionsContext;
use Oft\Service\FactoryInterface;
use Oft\Service\ServiceLocatorInterface;

class RenderOptionsContext implements FactoryInterface
{
    public function create(ServiceLocatorInterface $app)
    {
        $layoutConfig = $app->config['layout'];
        
        $renderOptions = new MvcRenderOptionsContext(array(
            'layoutTemplateName' => $layoutConfig['default'],
            'layoutTemplatePath' => $layoutConfig['path'],
        ));

        return $renderOptions;
    }

}