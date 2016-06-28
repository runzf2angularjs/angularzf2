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

use Oft\Install\Generate\Gir\LdapConfigGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GirConfigCommand extends Command
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('gir:config')
            ->setDescription('Génère un fichier de configuration pour la consulation de l\'annuaire groupe')
            
            ->addArgument('username', InputArgument::REQUIRED, 'Nom d\'utilisateur')
            ->addArgument('password', InputArgument::REQUIRED, 'Mot de passe')
            
            ->addOption('host', null, InputOption::VALUE_OPTIONAL, 'Adresse du serveur')
            ->addOption('port', null, InputOption::VALUE_OPTIONAL, 'Port')
            ->addOption('base-dn', null, InputOption::VALUE_OPTIONAL, 'Base DN')
            ->addOption('use-ssl', null, InputOption::VALUE_OPTIONAL, 'Utilisation de SSL (0 ou 1)')

            ->setHelp(<<<EOT
La commande <info>%command.name%</info> permet la génération d'un fichier de configuration pour la consulation de l'annuaire groupe
EOT
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {        
        $app = $this->getApplication()->getApp();
        
        $configAd = $app->config['gir']['ldap'];
        
        $generator = new LdapConfigGenerator();

        $generator->username = $input->getArgument('username');
        $generator->password = $input->getArgument('password');
                
        $generator->host = ($input->getOption('host') !== null) ?  $input->getOption('host') : $configAd['host'];
        $generator->baseDn = ($input->getOption('base-dn') !== null) ?  $input->getOption('base-dn') : $configAd['baseDn'];
        $generator->port = ($input->getOption('port') !== null) ?  $input->getOption('port') : $configAd['port'];
        $generator->useSsl = ($input->getOption('use-ssl') !== null) ?  $input->getOption('use-ssl') : $configAd['useSsl'];

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
