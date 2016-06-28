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

namespace Oft\Test\Install\Generate\Db;

use Oft\Install\Generate\Db\DbConfigGenerator;
use PHPUnit_Framework_TestCase;

class DbConfigGeneratorTest extends PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        if (!defined('APP_ROOT')) {
            define('APP_ROOT', '');
        }
    }

    public function testGenerate()
    {
        $generator = new DbConfigGenerator();

        $generator->user = 'user';
        $generator->password = 'pwd';
        $generator->host = 'host';
        $generator->dbname = 'dbname';
        $generator->charset = 'utf8';
        $generator->driver = 'pdo_mysql';
        $generator->unixsocket = null;
        $generator->port = 3306;
        $generator->driverOptions = array();

        $generator->generate();

        $files = $generator->getFiles();

        $this->assertCount(1, $files);
        $this->assertInstanceOf('Oft\Install\Generate\File', $files[0]);
    }

}
