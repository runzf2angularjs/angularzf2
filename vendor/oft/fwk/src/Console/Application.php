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

namespace Oft\Console;

use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Oft\Console\Tools\Formatter\WindowsFormatter;
use Oft\Db\NoDbConnection;
use Oft\Mvc\Application as App;
use Symfony\Component\Console\Application as SymfonyConsoleApp;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends SymfonyConsoleApp
{

    /**
     * @var App
     */
    protected $app;

    /**
     * Constructeur de l'application
     *
     * @todo version -> 1.0
     * @param array $mainConfig
     */
    public function __construct(array $mainConfig = array())
    {        
        $this->app = new App($mainConfig);
        $this->app->init();
        
        parent::__construct('OFT command-line tools', '1.0');
    }

    public function getDefaultHelperSet()
    {
        $helperSet = parent::getDefaultHelperSet();
        if (!$this->app->db instanceof NoDbConnection) {
            $helperSet->set(new ConnectionHelper($this->app->db));
        }

        return $helperSet;
    }

    public function getDefaultCommands()
    {
        $defaultCommands = parent::getDefaultCommands();

        $commands = $this->getApp()->config['commands'];
        foreach ($commands as $commandClass) {
            $defaultCommands[] = new $commandClass;
        }

        return $defaultCommands;
    }

    /**
     * Retourne l'instance d'application OFT
     *
     * @return App
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        if (DIRECTORY_SEPARATOR !== '/') {
            if (null === $output) {
                $output = new ConsoleOutput();
            }
            $output->setFormatter(new WindowsFormatter());
        }
        
        parent::run($input, $output);
    }
    
}
