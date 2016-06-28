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

use InvalidArgumentException;
use Oft\Install\Tools\MySql\Schema;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DbSchemaCommand extends Command
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('db:schema')
            ->setDescription('Crée ou supprime le schéma de base de données configuré')
            ->addArgument('action', InputArgument::REQUIRED, 'Action souhaitée')
            ->addArgument('username', InputArgument::REQUIRED, 'Nom d\'utilisateur administrateur')
            ->addArgument('password', InputArgument::OPTIONAL, 'Mot de passe')
            ->setHelp(<<<EOT
La commande <info>%command.name%</info> permet de créer ou supprimer le schéma de base de données configuré.

Cette commande nécessite de passer en paramètre :
 - L'action souhaitée sur le schéma précédemment configuré (configuration applicative)
 - Les informations d'authentification d'un administrateur du serveur MySQL

En complément, l'utilisateur spécifié dans la configuration sera créé si inexistant et ses droits seront positionnés.

    <info>%command.name% action username password</info>

Exemple de création du schéma à partir de l'utilisateur "root" sans mot de passe :

    <info>%command.name% create root</info>

Exemple de suppression du schéma à partir de l'utilisateur "root" et d'un mot de passe :

    <info>%command.name% drop root password</info>
EOT
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getApplication()->getApp();

        $action = $input->getArgument('action');

        $dbOptions = $app->db->getParams();
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');

        switch ($action) {
            case 'create':
                $messages = Schema::create($dbOptions, $username, $password);
                break;
            case 'drop':
                $messages = Schema::drop($dbOptions, $username, $password);
                break;
            default:
                throw new InvalidArgumentException('Action "' . $action . '" is not implemented yet');
        }

        $output->writeln($messages);
    }

}
