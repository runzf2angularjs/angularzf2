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

use Oft\Install\Generate\Module\ModuleGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateModuleCommand extends Command
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('generate:module')
            ->setDescription('Génère un nouveau module')
            ->addArgument('module-name', InputArgument::REQUIRED, 'Nom du module')
            ->setHelp(<<<EOT
La commande <info>%command.name%</info> permet la génération d'un nouveau module de l'application :

    <info>%command.name% module-name</info>

Le nom du module peut être précisé au format "CamelCase" (ModuleName) ou "dash" (module-name)

EOT
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getApplication()->getApp();
        
        $generator = new ModuleGenerator($app->moduleManager);

        $generator->moduleName = $input->getArgument('module-name');

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
