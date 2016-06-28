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

use Oft\Install\Generate\Crud\CrudGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCrudCommand extends Command
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('generate:crud')
            ->setDescription('Génère des classes pour la création d\'interfaces de gestion d\'une table de référence')
            ->addArgument('repository-class-name', InputArgument::REQUIRED, 'Nom du repository existant')
            ->addOption('class-name', null, InputOption::VALUE_OPTIONAL, 'Nom de la classe du contrôleur à générer')
            ->addOption('module-name', null, InputOption::VALUE_OPTIONAL, 'Nom du module de destination des fichiers générés')
            ->setHelp(<<<EOT
La commande <info>%command.name%</info> permet la génération des fichiers suivants pour la création d'interfaces de gestion d'une table de référence  :
    - d'une classe contrôleur et des vues associées
    - des classes formulaires (formulaire de recherche et formulaire de saisie)

La commande se base obligatoirement sur une classe repository existante, qui peut être générée avec la commande <info>generate:repository</info> :

    <info>%command.name% "App\Repository\CrudRepository"</info>

Vous pouvez préciser le nom de la classe (optionnel), le suffixe "Controller" sera ajouté automatiquement :

    <info>%command.name% <comment>--class-name=Crud</comment> "App\Repository\CrudRepository"</info>

Vous pouvez également préciser le nom du module de destination (optionnel, par défaut : module par défaut de l'application) :

    <info>%command.name% <comment>--module-name=App</comment> "App\Repository\CrudRepository"</info>

EOT
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getApplication()->getApp();

        $generator = new CrudGenerator($app->db, $app->moduleManager);
        
        $generator->repositoryClassName = $input->getArgument('repository-class-name');
        $generator->className = $input->getOption('class-name');
        $generator->moduleName = $input->getOption('module-name');

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
