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

namespace Oft\Install\Generate;

use Oft\Install\Generate\File;
use Oft\View\Resolver\DirectResolver;
use Oft\View\View;
use ReflectionClass;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Zend\View\Model\ViewModel;

abstract class GeneratorAbstract
{

    /**
     * Messages (erreurs, avertissement, succès)
     *
     * @var array
     */
    protected $messages = array();

    /**
     * Types de messages gérés
     *
     * @var array
     */
    protected $messageStyles = array('info', 'comment', 'question', 'error');

    /**
     * Fichiers à créer
     *
     * @var array
     */
    protected $toCreate = array();

    /**
     * Fichiers à ignorer
     *
     * @var array
     */
    protected $toSkip = array();

    /**
     * Fichiers à écraser
     *
     * @var array
     */
    protected $toOverwrite = array();

    /**
     * Génération du code
     * Retourne les fichiers qui seront générés
     *
     * @return array
     */
    public abstract function generate();

    /**
     * Ajoute un fichier à la liste des fichiers à traiter
     *
     * @param File $file
     */
    public function addFile(File $file)
    {
        switch ($file->getOperation()) {
            case File::CREATE:
                $this->toCreate[] = $file;
                break;
            case File::OVERWRITE:
                $this->toOverwrite[] = $file;
                break;
            case File::SKIP:
                $this->toSkip[] = $file;
                break;
        }
    }

    /**
     * Retourne les fichiers, selon un type donné
     * Si le type n'est pas fourni, retourne tous les fichiers
     *
     * @param string $type
     * @return array
     */
    public function getFiles($type = null)
    {
        if ($type) {
            return $this->{'to' . ucfirst($type)};
        }

        return array_merge(
            $this->toCreate,
            $this->toSkip,
            $this->toOverwrite
        );
    }

    /**
     * Ajoute un message
     *
     * @param string $message
     * @param string|null $style
     * @return void
     */
    public function addMessage($message, $style = null)
    {
        if (in_array($style, $this->messageStyles)) {
            $message = '<' . $style . '>' . $message . '</' . $style . '>';
        }

        $this->messages[] = $message;
    }

    /**
     * Retourne les messages
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Ajoute le message de confirmation
     */
    public function addSuccessMessage()
    {
        $this->addMessage("Opération terminée avec succès", 'info');
    }

    /**
     * Ajoute le message d'annulation de l'opération
     */
    public function addCancelMessage()
    {
        $this->addMessage("Opération annulée", 'info');
    }

    /**
     * Retourne le répertoire des fichiers modèles
     *
     * @return string
     */
    public function getTemplateDir()
    {
        $class = new ReflectionClass($this);

        return dirname($class->getFileName()) . '/template';
    }

    /**
     * Sauvegarde des fichiers générés
     *
     * @return bool
     */
    public function save()
    {
        $hasError = false;

        // Fichiers à créer et écraser
        $files = array_merge($this->toCreate, $this->toOverwrite);
        
        foreach ($files as $file) {
            $operation = $file->getOperation();
            $relativePath = $file->getPath();

            if ($operation === File::OVERWRITE) {
                $result = $file->backup();
                if (is_string($result)) {
                    $hasError = true;
                    $this->addMessage(" (erreur) " . $file->getBackupPath() . "\n $result", 'error');
                    continue;
                }
            }
            
            $result = $file->save();
            if (is_string($result)) {
                $hasError = true;
                $this->addMessage(" (erreur) $relativePath\n $result", 'error');
                continue;
            }

            if ($operation === File::CREATE) {
                $this->addMessage(' (créé) ' . $relativePath);
            } else {
                $this->addMessage(' (écrasé) ' . $relativePath);
                if ($file->shouldBackup()) {
                    $this->addMessage(' -> (backup) ' . $file->getBackupPath());
                }
            }
        }

        return !$hasError;
    }

    /**
     * Demande la confirmation de la création des fichiers
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param QuestionHelper $questionHelper
     * @return bool
     */
    public function confirm(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper)
    {
        // Flag de demande confirmation
        $confirm = false;

        if (count($this->toCreate)) {
            $confirm = true;
            $output->writeln("Les fichiers suivants vont être créés :");
            /* @var $file File */
            foreach ($this->toCreate as $file) {
                $output->writeln('<comment> ' . $file->getPath() . '</comment>');
            }
        }

        if (count($this->toOverwrite)) {
            $confirm = true;
            $output->writeln("Les fichiers suivants vont être écrasés :");
            /* @var $file File */
            foreach ($this->toOverwrite as $file) {
                $output->writeln('<comment> ' . $file->getPath() . '</comment>');
                if ($file->shouldBackup()) {
                    $output->writeln('<comment> -> (backup) ' . $file->getBackupPath() . '</comment>');
                }
            }
        }

        if (count($this->toSkip)) {
            $output->writeln("Les fichiers suivants ne seront pas affectés :");
            /* @var $file File */
            foreach ($this->toSkip as $file) {
                $output->writeln('<comment> ' . $file->getPath() . '</comment>');
            }
        }
        
        if ($input->isInteractive() === false) {
            return true;
        }

        if ($confirm === false) {
            return true;
        }

        $question = new Question('Veuillez confirmer (y/yes) : ', 'n');
        $confirmation = $questionHelper->ask($input, $output, $question);

        if ($confirmation === 'y' || $confirmation === 'yes') {
            return true;
        }

        return false;
    }

    /**
     * Procède au rendu d'un modèle
     *
     * @param type $template
     * @param array $variables
     * @return string
     */
    public function render($template, array $variables = array())
    {
        $view = new View();

        $resolver = new DirectResolver($this->getTemplateDir(), 'tpl');
        $view->setResolver($resolver);

        $viewModel = new ViewModel();

        $viewModel
            ->setTemplate($template)
            ->setVariables($variables);

        return $view->render($viewModel);
    }

}
