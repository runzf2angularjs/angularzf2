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

namespace Oft\Test\Mock\Module;

use Oft\Module\ModuleInterface;
use Oft\Mvc\Application;

class Module implements ModuleInterface
{

    public function getName()
    {
        return 'oft-test';
    }

    public function getConfig($cli = false)
    {
        return array(
            'name' => 'value',
            'services' => array(
                'invokables' => array(),
                'factories' => array(),
            ),
            'middlewares' => array(
                'Oft\\Test\\Mock\\Middleware'
            ),
        );
    }

    public function getDir($type = null)
    {
        return __DIR__ . '/../..';
    }

    public function init(Application $app)
    {
        
    }
}
