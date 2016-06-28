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

namespace Oft\Db;

use Doctrine\DBAL\Driver\Connection;
use PDO;

/**
 * Spécifie qu'aucune opération n'est possible sur le SGBD par défaut
 *
 * Envoie une exception sur :
 *  - toute opération de l'interface 'Doctrine\DBAL\Driver\Connection'
 *  - createQueryBuilder (classe 'Doctrine\DBAL\Connection')
 */
class NoDbConnection implements Connection
{

    public function beginTransaction()
    {
        throw new \RuntimeException("Not implemented");
    }

    public function commit()
    {
        throw new \RuntimeException("Not implemented");
    }

    public function errorCode()
    {
        throw new \RuntimeException("Not implemented");
    }

    public function errorInfo()
    {
        throw new \RuntimeException("Not implemented");
    }

    public function exec($statement)
    {
        throw new \RuntimeException("Not implemented");
    }

    public function lastInsertId($name = null)
    {
        throw new \RuntimeException("Not implemented");
    }

    public function prepare($prepareString)
    {
        throw new \RuntimeException("Not implemented");
    }

    public function query()
    {
        throw new \RuntimeException("Not implemented");
    }

    public function quote($input, $type = PDO::PARAM_STR)
    {
        throw new \RuntimeException("Not implemented");
    }

    public function rollBack()
    {
        throw new \RuntimeException("Not implemented");
    }

    public function createQueryBuilder()
    {
        throw new \RuntimeException("Not implemented");
    }
}
