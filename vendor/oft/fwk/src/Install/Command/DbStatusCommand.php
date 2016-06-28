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

namespace Oft\Install\Command;

use Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DbStatusCommand extends StatusCommand
{

    /**
     * {@inheritdoc}
     */
    protected function getMigrationConfiguration(InputInterface $input, OutputInterface $output)
    {
        return $this->getApplication()->getApp()
            ->get('DoctrineMigrations');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('db:status');
    }
    
}
