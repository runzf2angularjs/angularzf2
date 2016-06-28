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

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Registry;
use Oft\Mvc\Application;
use Oft\Service\FactoryInterface;
use Oft\Service\ServiceLocatorInterface;

class Log implements FactoryInterface
{

    /**
     * Créé et configure les loggers
     * Retourne le canal de log par défaut
     *
     * @param Application $app
     * @return Logger
     */
    public function create(ServiceLocatorInterface $app)
    {
        foreach ($app->config['log'] as $channelName => $channelConfig) {
            // Infos
            $filenameFormat = $channelConfig['format']['filename'];
            $dateFormat = $channelConfig['format']['date'];

            // Handler
            $handler = new RotatingFileHandler($channelConfig['filename'], 0, $channelConfig['level']);
            $handler->setFilenameFormat($filenameFormat, $dateFormat);
            $handler->setFormatter(
                new LineFormatter(null, null, true, true)
            );

            // Logger
            $logger = new Logger($channelName, array($handler));

            // Save & overwrite defaults
            Registry::addLogger($logger, $channelName, true);
        }

        return Registry::getInstance('default');
    }

}
