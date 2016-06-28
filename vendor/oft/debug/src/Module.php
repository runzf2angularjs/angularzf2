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

namespace Oft\Debug;

use DebugBar\Bridge\DoctrineCollector;
use DebugBar\Bridge\MonologCollector;
use DebugBar\DataCollector\ConfigCollector;
use DebugBar\StandardDebugBar;
use Doctrine\DBAL\Logging\DebugStack;
use Monolog\Registry;
use Oft\Db\NoDbConnection;
use Oft\Module\ModuleInterface;
use Oft\Mvc\Application;

/**
 * Classe de définition du module Debug
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class Module implements ModuleInterface
{

    const REPLACE_TAG = '<!-- DEBUG_BAR -->';

    public function getName()
    {
        return 'debug';
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
        $config = $app->config;
        $modules = $app->moduleManager->getModules();
        $defaultModule = $app->moduleManager->getDefault();

        // Barre de debug
        $debugBar = new StandardDebugBar();

        // Configuration amenée par les modules
        $debugBar->addCollector(new ConfigCollector($config, 'Config'));

        // Liste des modules configurés
        $modulesInfos = [];
        foreach ($modules as $name => $instance) {
            $default = ($name === $defaultModule) ? ' (default)' : '';
            $modulesInfos[ $name . $default ] = \get_class($instance) . ' in ' . \realpath($instance->getDir());
        }
        $debugBar->addCollector(new ConfigCollector($modulesInfos, 'Modules'));

        // Requêtes SQL
        if (!$app->db instanceof NoDbConnection) {
            $debugStack = new DebugStack();
            $app->db->getConfiguration()->setSQLLogger($debugStack);
            $debugBar->addCollector(new DoctrineCollector($debugStack));
        }

        // Loggers configurés
        foreach ($config['log'] as $channelName => $channelConfig) {
            $debugBar->addCollector(
                new MonologCollector(
                    Registry::getInstance($channelName),
                    $channelConfig['level'],
                    true,
                    'monolog - ' . $channelName
                )
            );
        }

        $debugService = $app->get('Debug');
        $debugService->setDebugBar($debugBar);

        $app->view->debugBar()->setDebugBar($debugBar);
    }
}
