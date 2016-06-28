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

use Oft\Install\Generate\Db\DbConfigGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DbConfigCommand extends Command
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('db:config')
            ->setDescription('Génère un fichier de configuration pour la base de données')
            
            ->addArgument('host', InputArgument::REQUIRED, 'Adresse du serveur')
            ->addArgument('dbname', InputArgument::REQUIRED, 'Nom de la base de données')
            ->addArgument('user', InputArgument::REQUIRED, 'Nom d\'utilisateur')
            ->addArgument('password', InputArgument::OPTIONAL, 'Mot de passe')
          
            ->addOption('port', null, InputOption::VALUE_OPTIONAL, 'Numéro de port')
            ->addOption('charset', null, InputOption::VALUE_OPTIONAL, 'Encodage')
            ->addOption('driver', null, InputOption::VALUE_OPTIONAL, 'Type de driver')
            ->addOption('unix-socket', null, InputOption::VALUE_OPTIONAL, 'Socket Unix')
            ->setHelp(<<<EOT
La commande <info>%command.name%</info> permet la génération d'un fichier de configuration pour la base de données
EOT
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {        
        $generator = new DbConfigGenerator();

        $generator->host = $input->getArgument('host');
        $generator->dbname = $input->getArgument('dbname');
        $generator->user = $input->getArgument('user');
        $generator->password = $input->getArgument('password');
        
        $generator->port = $input->getOption('port');
        $generator->charset = $input->getOption('charset');
        $generator->driver = $input->getOption('driver');
        $generator->unixsocket = $input->getOption('unix-socket');

        $generator->generate();

        /* @var $question QuestionHelper */
        $question = $this->getHelperSet()->get('question');
        $answer = $generator->confirm($input, $output, $question);

        if ($answer === true) {
            if ($generator->save() === true) {
                $generator->addSuccessMessage();
            }
        } else {
            $generator->addCancelMessage();
        }

        $output->write($generator->getMessages(), true);
    }

}
