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

namespace Oft\Gassi;

use Oft\Module\ModuleInterface;
use Oft\Mvc\Application;

/**
 * Classe de définition du module Gassi
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class Module implements ModuleInterface
{
    
    public function getName()
    {
        return 'oft-gassi';
    }

    public function getConfig($cli = false)
    {
        return include __DIR__ . '/../config/config.php';
    }

    public function getDir($type = null)
    {
        $dir = __DIR__ . '/..';

        if ($type !== null) {
            $dir .= '/' . $type;
        }

        return $dir;
    }

    public function init(Application $app)
    {
    }

}
