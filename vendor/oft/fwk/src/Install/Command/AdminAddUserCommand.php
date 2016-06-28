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

use Oft\Util\String;
use Oft\Install\Generate\Admin\AdminGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AdminAddUserCommand extends Command
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('admin:add-user')
            ->setDescription('Add an admin user')
            ->addArgument('username', InputArgument::REQUIRED, 'Nom de l\'utilisateur')
            ->addArgument('password', InputArgument::REQUIRED, 'Mot de passe')
            ->addOption('module-name', null, InputOption::VALUE_OPTIONAL, 'Nom du module de destination de la classe')
            ->setHelp(<<<EOT
Add an admin user
EOT
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getApplication()->getApp();
        
        $moduleManager = $app->moduleManager;
        
        $username = $input->getArgument('username');
        $regex = "#Version_" . String::stringToValidClassName($username) ."_[0-9]{14}.php$#i";
        
        $alreadyExists = false;
        foreach($moduleManager->getModules() as $module) {
            $search = glob($module->getDir('sql') . '/Version*.php');
            foreach($search as $filename) {
                if (preg_match($regex, $filename)) {
                    $alreadyExists = true;
                }
            }
        }
        
        $generator = new AdminGenerator($moduleManager);
        
        if ($alreadyExists) {
            $generator->addMessage("Cet administrateur existe déjà", 'info');
            $output->write($generator->getMessages(), true);
        } else {
            $generator->username = strtolower($input->getArgument('username'));
            $generator->password = $input->getArgument('password');
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

}
