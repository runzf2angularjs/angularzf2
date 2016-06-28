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

namespace Oft\Install\Generate\Db;

use Oft\Install\Generate\File;
use Oft\Install\Generate\GeneratorAbstract;

class DbConfigGenerator extends GeneratorAbstract
{

    /**
     * Host
     *
     * @var string
     */
    public $host;

    /**
     * Dbname
     *
     * @var string
     */
    public $dbname;

    /**
     * User
     *
     * @var string
     */
    public $user;

    /**
     * Password
     *
     * @var string
     */
    public $password;

    /**
     * Port
     *
     * @var string
     */
    public $port;

    /**
     * Charset
     *
     * @var string
     */
    public $charset;

    /**
     * Driver
     *
     * @var string
     */
    public $driver;

    /**
     * Socket unix
     *
     * @var string
     */
    public $unixsocket;

    /**
     * Options du driver
     *
     * @var string
     */
    public $driverOptions;

    /**
     * Génération du code
     *
     * Alimente le tableau des fichiers à créer, écraser et ignorer
     */
    public function generate()
    {
        $template = 'config';

        $destination = APP_ROOT . '/application/config/config.db.php';
        
        $content = $this->render($template, array(
            'user' => $this->user,
            'password' => $this->password,
            'host' => $this->host,
            'dbname' => $this->dbname,
            'charset' => $this->charset,
            'driver' => $this->driver,
            'unixsocket' => $this->unixsocket,
            'port' => $this->port,
            'driverOptions' => $this->driverOptions,
        ));

        $this->addFile(new File($destination, $content));
    }

}
