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

class Constants
{
    /**
     * Définit les constantes principales
     *
     * Définit APP_ENV et DATA_DIR
     */
    public static function defineMain()
    {
        if (!defined('DATA_DIR')) {
            if (getenv('DATA_DIR')) {
                define('DATA_DIR', getenv('DATA_DIR'));
            } else {
                define('DATA_DIR', constant('APP_ROOT') . DIRECTORY_SEPARATOR . 'data');
            }
        }

        if (!defined('APP_ENV') && getenv('APP_ENV')) {
            define('APP_ENV', strtolower(basename(getenv('APP_ENV'))));
        }
    }

    /**
     * Définit les autres constantes utiles à partir des constantes gérées
     *
     * Définit TEMP_DIR, LOG_DIR, CACHE_DIR, PUBLIC_DIR, DS, PS
     *
     * @return void
     */
    public static function defineOthers()
    {
        // Répertoire temporaire
        if (!defined('TEMP_DIR')) {
            define('TEMP_DIR', constant('DATA_DIR') . '/tmp');
        }

        // Répertoire des logs
        if (!defined('LOG_DIR')) {
            define('LOG_DIR', constant('DATA_DIR') . '/logs');
        }

        // Répertoire du cache
        if (!defined('CACHE_DIR')) {
            define('CACHE_DIR', constant('DATA_DIR') . '/cache');
        }

        // Répertoire du cache
        if (!defined('UPLOAD_DIR')) {
            define('UPLOAD_DIR', constant('DATA_DIR') . '/upload');
        }

        // DocumentRoot
        if (!defined('PUBLIC_DIR')) {
            define('PUBLIC_DIR', constant('APP_ROOT') . '/public');
        }

        // Raccourci pour DIRECTORY_SEPARATOR
        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }

        // Raccourci pour PATH_SEPARATOR
        if (!defined('PS')) {
            define('PS', PATH_SEPARATOR);
        }
    }

    /**
     * Coordonne l'éxécution des méthodes de cette classe
     *
     * @return void
     */
    public static function init()
    {
        self::defineMain();
        self::defineOthers();
    }

}
