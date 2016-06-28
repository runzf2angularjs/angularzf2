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

namespace Oft\Util;

use Oft\Mvc\Application;

// Inclusion des fichiers de fonctions
require_once __DIR__ . '/../functions/log.php';
require_once __DIR__ . '/../functions/escaper.php';
require_once __DIR__ . '/../functions/translator.php';

class Functions
{

    /**
     * Conteneur d'application
     *
     * @var Application
     */
    protected static $app;

    /**
     * Initialisation
     *
     * @param Application $app
     * @return void
     */
    public static function setApp(Application $app = null)
    {
        self::$app = $app;
    }

    /**
     * 
     * @return Application
     */
    public static function getApp()
    {
        return self::$app;
    }

}
