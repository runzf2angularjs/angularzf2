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

namespace Oft\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DbTablesCommand extends Command
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('db:tables')
            ->setDescription('Liste les tables de la base de données')
            ->setHelp(<<<EOT
La commande <info>%command.name%</info> permet de lister les tables de la base de données :

    <info>%command.name%</info>

EOT
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getApplication()->getApp();

        $table = new Table($output);

        $tables = $app->db->executeQuery('show tables');

        $table
            ->setHeaders(array('tables in ' . $app->db->getDatabase()))
            ->addRows($tables->fetchAll());

        $table->render($output);
    }

}
