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

use Oft\Install\Generate\Repository\RepositoryGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateRepositoryCommand extends Command
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('generate:repository')
            ->setDescription('Génère une classe repository (entrepôt)')
            ->addArgument('table-name', InputArgument::REQUIRED, 'Nom de la table ciblée')
            ->addOption('class-name', null, InputOption::VALUE_OPTIONAL, 'Nom de la classe du repository à générer')
            ->addOption('module-name', null, InputOption::VALUE_OPTIONAL, 'Nom du module de destination de la classe')
            ->setHelp(<<<EOT
La commande <info>%command.name%</info> permet la génération d'une classe repository (entrepôt) :

    <info>%command.name% table_name</info>

Vous pouvez préciser le nom de la classe (optionnel), le suffixe "Repository" sera ajouté automatiquement :

    <info>%command.name% <comment>--class-name=Crud</comment> table_name</info>

Vous pouvez également préciser le nom du module de destination (optionnel, par défaut : module par défaut de l'application) :

    <info>%command.name% <comment>--module-name=App</comment> table_name</info>

EOT
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getApplication()->getApp();
        
        $generator = new RepositoryGenerator($app->db, $app->moduleManager);

        $generator->tableName = $input->getArgument('table-name');
        $generator->moduleName = $input->getOption('module-name');
        $generator->className = $input->getOption('class-name');

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
