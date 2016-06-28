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

use ErrorException;
use Oft\Module\ModuleManager;
use Oft\Util\String;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class AdminDeleteUserCommand extends Command
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('admin:delete-user')
            ->setDescription('Delete an admin user')
            ->addArgument('username', InputArgument::REQUIRED, 'Nom de l\'utilisateur')
            ->setHelp(<<<EOT
Delete an admin user
EOT
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getApplication()->getApp();
        
        /* @var $moduleManager ModuleManager */
        $moduleManager = $app->moduleManager;
        
        $configuration = $app->get('DoctrineMigrations');

        $migrated = $configuration->getMigratedVersions();
        
        $username = $input->getArgument('username');
        $regex = "#Version_" . String::stringToValidClassName($username) ."_[0-9]{14}.php$#";

        $files = array();
        $filesMigrated = array();
        foreach($moduleManager->getModules() as $module) {
            $search = glob($module->getDir('sql') . '/Version*.php');
            foreach($search as $filename) {
                if (preg_match($regex, $filename)) {
                    $version = substr($filename, -18, -4);
                    
                    if (in_array($version, $migrated)) {
                        $filesMigrated[] = $filename;
                    } else {
                        $files[] = $filename;
                    }
                }
            }
        }
        
        if (count($filesMigrated) > 0) {
            $output->writeln("La classe de migration pour ce nom a déjà été migrée");
            $output->writeln("Veuillez revenir à une version précédente via la commande 'db:migrate'");
            return;
        }
        
        if (count($files) == 0) {
            $output->writeln("Aucune classe de migration n'a été trouvée pour ce nom");
            return;
        }

        $output->writeln("Liste du(es) fichier(s) trouvé(s) :");
        foreach ($files as $filename) {
            $output->writeln('<comment> ' . $filename . '</comment>');
        }

        /* @var $helper QuestionHelper */
        $helper = $this->getHelperSet()->get('question');
        $question = new Question('Veuillez confirmer la suppression du(es) fichier(s) (y/yes) : ', 'n');
        $confirmation = $helper->ask($input, $output, $question);

        if ($confirmation === 'y' || $confirmation === 'yes') {
            foreach ($files as $filename) {
                try {
                    unlink($filename);
                } catch (ErrorException $e) {
                    $output->writeln('<error>Le fichier ' . $filename . ' n\'a pas pu être supprimé</error>');
                }
            }
            $output->writeln('<info>Opération terminée</info>');
        } else {
            $output->writeln('<info>Opération annulée</info>');
        }

    }

}
