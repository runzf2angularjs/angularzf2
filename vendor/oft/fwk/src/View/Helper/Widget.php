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

namespace Oft\View\Helper;

use Zend\View\Helper\AbstractHelper;

class Widget extends AbstractHelper
{
    public function __invoke($name, array $context = array())
    {
        $widget = $this->view->app
            ->get('Widget')
            ->get($name);
        
        return call_user_func_array($widget, $context);
    }
}
