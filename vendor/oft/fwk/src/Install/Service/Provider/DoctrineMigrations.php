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

namespace Oft\Install\Service\Provider;

use Oft\Install\Tools\DbMigrate\Configuration;
use Oft\Mvc\Application;
use Oft\Service\FactoryInterface;
use Oft\Service\ServiceLocatorInterface;

class DoctrineMigrations implements FactoryInterface
{

    /**
     * Construit l'objet de configuration pour doctrine\migrations
     *
     * @param Application $app
     * @return Configuration
     */
    public function create(ServiceLocatorInterface $app)
    {
        $config = $app->config['migrations'];

        // Modules
        $modules = array_keys($app->moduleManager->getModules());

        // Informations du module par défaut
        $defaultModule = $app->moduleManager->getDefault();
        $defaultPath = null;
        $defaultNamespace = null;

        // Configuration
        $configuration = new Configuration($app->db);
        $configuration->setMigrationsTableName($config['table']);

        foreach ($modules as $moduleName) {
            // Répertoire des classes du module
            $path = $app->moduleManager->getModule($moduleName)->getDir('sql');

            // Namespace du module
            $namespace = $app->moduleManager->getModuleNamespace($moduleName);
            $namespace .= '\Sql';

            // Enregistrement du module par défaut à part
            if ($moduleName === $defaultModule) {
                $defaultPath = $path;
                $defaultNamespace = $namespace;
            }
            
            // Enregistrement des classes de migration du module
            $configuration->setMigrationsNamespace($namespace);
            $configuration->registerMigrationsFromDirectory($path);
        }

        // Module par défaut -> emplacement par défaut des classes générées
        $configuration->setMigrationsNamespace($defaultNamespace);
        $configuration->setMigrationsDirectory($defaultPath);

        return $configuration;
    }

}
