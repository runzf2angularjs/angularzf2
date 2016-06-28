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

namespace Oft\Install\Tools\DbMigrate;

use Doctrine\DBAL\Migrations\Configuration\Configuration as DoctrineConfiguration;

class Configuration extends DoctrineConfiguration
{

    /**
     * Enregistrement des classes de migration d'un répertoire donné
     *
     * Surcharge pour chercher les 14 derniers caractères du nom de la classe
     * et non à partir du 7ème (recherche du numéro de version).
     * Cela permet de préfixer librement le nom des classes.
     *
     * @param string $path
     * @return array
     */
    public function registerMigrationsFromDirectory($path)
    {        
        $path = realpath($path);
        $path = rtrim($path, '/');
        $files = glob($path . '/Version*.php');
        $versions = array();
        if ($files) {
            foreach ($files as $file) {
                require_once $file;
                $info = pathinfo($file);
                // Allow to add something between Version and Datetime in filename
                $version = substr($info['filename'], -14);
                $class = $this->getMigrationsNamespace() . '\\' . $info['filename'];
                $versions[] = $this->registerMigration($version, $class);
            }
        }

        return $versions;
    }
    
}
