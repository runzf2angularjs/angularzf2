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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Oft\Mvc\Application;
use Oft\Service\FactoryInterface;
use Oft\Service\ServiceLocatorInterface;

class Db implements FactoryInterface
{

    /**
     * Instancie et configure l'objet de connexion à la base de données
     *
     * @param Application $app
     * @return Connection
     */
    public function create(ServiceLocatorInterface $app)
    {
        $dbConfig = $app->config['db'];

        if (!isset($dbConfig['wrapperClass'])) {
            $dbConfig['wrapperClass'] = 'Oft\Db\Connection';
        }

        return DriverManager::getConnection($dbConfig);
    }

}
