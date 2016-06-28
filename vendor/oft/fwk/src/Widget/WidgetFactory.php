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
use Oft\Service\ServiceManager;

class WidgetFactory extends ServiceManager
{
    /** @var Application */
    protected $app;

    public function __construct(Application $app)
    {
        parent::__construct($app->config['widgets']);
        
        $this->app = $app;
    }

    public function getApp()
    {
        return $this->app;
    }
}
