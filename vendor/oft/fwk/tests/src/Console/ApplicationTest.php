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

namespace Oft\Test\Console;

use Oft\Console\Application as CliApplication;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Command\Command;

class TestCommand extends Command
{
    public function __construct($name = null)
    {
        parent::__construct('TestCommand');
    }
}

class ApplicationTest extends PHPUnit_Framework_TestCase
{

    public function getCliApplication($config = array())
    {
        $mainConfig = array(
            'debug' => true, // NO CACHE
            'services' => array(
                'Log' => 'stdClass',
                'Translator' => 'stdClass',
                'Db' => 'Oft\Db\NoDbConnection'
            ),
            'commands' => array()
        );

        $appConfig = \Oft\Util\Arrays::mergeConfig($mainConfig, $config);

        return new CliApplication($appConfig);
    }

    public function testGetApp()
    {
        $console = $this->getCliApplication();

        $app = $console->getApp();

        $this->assertInstanceOf('Oft\Mvc\Application', $app);
    }

    public function testSetCommands()
    {
        $config = array(
            'commands' => array( // Commande de test
                'Oft\Test\Console\TestCommand',
            ),
        );

        $console = $this->getCliApplication($config);
        
        $this->assertInstanceOf(
            'Oft\Test\Console\TestCommand',
            $console->get('TestCommand')
        );
    }
    
}
