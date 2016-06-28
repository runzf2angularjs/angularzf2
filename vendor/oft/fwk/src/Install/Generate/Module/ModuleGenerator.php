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

namespace Oft\Install\Generate\Module;

use DomainException;
use InvalidArgumentException;
use Oft\Install\Generate\File;
use Oft\Install\Generate\GeneratorAbstract;
use Oft\Module\ModuleManager;
use Oft\Util\String;

class ModuleGenerator extends GeneratorAbstract
{

    /**
     * Nom du module de destination
     *
     * @var string
     */
    public $moduleName;

    /**
     * ModuleManager
     *
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * Modules
     *
     * @var array
     */
    protected $modules = array();

    /**
     * @param ModuleManager $moduleManager
     */
    public function __construct(ModuleManager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
        $this->modules = array_keys($moduleManager->getModules());
    }

    /**
     * Retourne le nom du module à créer
     *
     * @return string
     */
    public function getModuleName()
    {
        if ($this->moduleName === null) {
            throw new InvalidArgumentException(
                'Le nom du module n\'a pas été précisé' .
                'ex: module-name'
            );
        }

        $this->moduleName = str_replace('_', '-', String::camelCaseToDash($this->moduleName));

        if (\in_array($this->moduleName, $this->modules)) {
            throw new InvalidArgumentException('Le module ' . $this->moduleName . ' existe déjà');
        }

        return $this->moduleName;
    }

    /**
     * Retourne le namespace du module
     *
     * @return string
     */
    public function getModuleNamespace()
    {
        return String::dashToCamelCase($this->getModuleName());
    }

    /**
     * Retourne le chemin de la racine du module
     *
     * @return string
     */
    public function getModuleRoot()
    {
        return APP_ROOT . '/modules/' . $this->getModuleName();
    }

    /**
     * Génération du code
     *
     * Alimente le tableau des fichiers à créer, écraser et ignorer
     */
    public function generate()
    {
        $this->addFile($this->getModuleFile());

        $this->addFile($this->getBootstrapTestFile());
        $this->addFile($this->getModuleTestFile());
        $this->addFile($this->getPhpunitXmlFile());
        
        $this->addFile($this->getConfigFile());
        $this->addFile($this->getGitignoreForSqlFile());
        $this->addFile($this->getControllerFile());
        $this->addFile($this->getViewFile());
    }

    /**
     * Génération de la classe Module
     *
     * @return File
     */
    protected function getModuleFile()
    {
        $moduleName = $this->getModuleName();
        $moduleNamespace = $this->getModuleNamespace();

        $template = 'module';
        $destination = $this->getModuleRoot() . '/src/Module.php';

        $content = $this->render($template, array(
            'namespace' => $moduleNamespace,
            'moduleName' => $moduleName,
        ));

        return new File($destination, $content);
    }

    /**
     * Génération du fichier de bootstrap des tests
     *
     * @return File
     */
    protected function getBootstrapTestFile()
    {
        $moduleNamespace = $this->getModuleNamespace();

        $template = 'tests-bootstrap';
        $destination = $this->getModuleRoot() . '/tests/bootstrap.php';

        $content = $this->render($template, array(
            'namespace' => $moduleNamespace,
        ));

        return new File($destination, $content);
    }

    /**
     * Génération de la classe de test de la classe Module
     *
     * @return File
     */
    protected function getModuleTestFile()
    {
        $moduleName = $this->getModuleName();
        $moduleNamespace = $this->getModuleNamespace();

        $template = 'moduleTest';
        $destination = $this->getModuleRoot() . '/tests/src/ModuleTest.php';

        $content = $this->render($template, array(
            'namespace' => $moduleNamespace,
            'moduleName' => $moduleName,
        ));

        return new File($destination, $content);
    }

    /**
     * Génération du fichier XML de configuration de PHPUnit
     *
     * @return File
     */
    protected function getPhpunitXmlFile()
    {
        $template = 'tests-phpunit-xml';
        $destination = $this->getModuleRoot() . '/phpunit.xml';

        $content = $this->render($template);

        return new File($destination, $content);
    }

    /**
     * Génération du fichier de configuration
     *
     * @return File
     */
    protected function getConfigFile()
    {
        $template = 'config';
        $destination = $this->getModuleRoot() . '/config/config.php';

        $content = $this->render($template);

        return new File($destination, $content);
    }

    /**
     * Génération du fichier "gitignore" pour le répertoire vide "sql"
     *
     * @return File
     */
    protected function getGitignoreForSqlFile()
    {
        $template = 'sql-gitignore';
        $destination = $this->getModuleRoot() . '/sql/.gitignore';

        $content = $this->render($template);

        return new File($destination, $content);
    }

    /**
     * Génération du contrôleur
     *
     * @return File
     */
    protected function getControllerFile()
    {
        $moduleNamespace = $this->getModuleNamespace();

        $template = 'controller';
        $destination = $this->getModuleRoot() . '/src/Controller/IndexController.php';

        $content = $this->render($template, array(
            'namespace' => $moduleNamespace,
        ));

        return new File($destination, $content);
    }

    /**
     * Génération de la vue associée au contrôleur
     *
     * @return File
     */
    protected function getViewFile()
    {
        $moduleName = $this->getModuleName();
        $moduleNamespace = $this->getModuleNamespace();

        $template = 'index';
        $destination = $this->getModuleRoot() . '/views/index/index.phtml';

        $content = $this->render($template, array(
            'namespace' => $moduleNamespace,
            'moduleName' => $moduleName,
        ));

        return new File($destination, $content);
    }

}
