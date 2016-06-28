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

namespace Oft\Install\Generate\Crud;

use Doctrine\DBAL\Connection;
use DomainException;
use InvalidArgumentException;
use Oft\Install\Generate\File;
use Oft\Install\Generate\GeneratorAbstract;
use Oft\Module\ModuleManager;
use Oft\Util\String;
use Oft\View\View;

class CrudGenerator extends GeneratorAbstract
{

    /**
     * Classe de description de la table
     *
     * @var string
     */
    public static $tableDescClassName = 'Oft\Install\Tools\MySql\TableDescription';

    /**
     * Nom de la classe repository
     *
     * @var string
     */
    public $repositoryClassName;

    /**
     * Nom de la classe contrôleur générée
     *
     * @var string
     */
    public $className;

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
     * Connexion BdD
     *
     * @var Connection
     */
    protected $db;

    /**
     * Description de la table
     *
     * @var TableDescription
     */
    protected $tableDescription;

    /**
     * Modules
     *
     * @var array
     */
    protected $modules = array();

    /**
     * @param View $view
     * @param Connection $db
     * @param ModuleManager $moduleManager
     */
    public function __construct(Connection $db, ModuleManager $moduleManager)
    {
        $this->db = $db;
        $this->moduleManager = $moduleManager;
        $this->modules = array_keys($moduleManager->getModules());
    }

    /**
     * Retourne le nom de la classe repository
     * Retourne le nom complet si $fullname est VRAI, sinon seul le nom de la classe
     *
     * @param bool $fullname
     * @return string
     */
    public function getRepositoryClassName($fullname = false)
    {
        if ($this->repositoryClassName === null) {
            throw new InvalidArgumentException(
                'Le nom de la classe repository n\'a pas été précisé' .
                'ex: App\Repository\CrudRepository'
            );
        }

        $pos = strrpos($this->repositoryClassName, '\\');

        if ($pos === false) {
            throw new InvalidArgumentException(
                'Le nom de la classe repository doit comporter son namespace, ' .
                'ex: App\Repository\CrudRepository'
            );
        }

        if (class_exists($this->repositoryClassName) === false) {
            throw new InvalidArgumentException('La classe repository ne semble pas exister');
        }

        if ($fullname === false) {
            return substr($this->repositoryClassName, $pos + 1);
        } else {
            return $this->repositoryClassName;
        }
    }

    /**
     * Retourne le nom du module cible
     * Sélectionne le module par défaut si la valeur est absente
     *
     * @return string
     */
    public function getModuleName()
    {
        if ($this->moduleName === null) {
            $this->moduleName = $this->moduleManager->getDefault();
        }

        if (!\in_array($this->moduleName, $this->modules)) {
            throw new DomainException('Le module ' . $this->moduleName . ' n\'existe pas');
        }

        return $this->moduleName;
    }

    /**
     * Retourne le nom de la classe à partir du nom de la table ciblée
     * Concatène le type donné (ex: "Crud" + "Controller")
     *
     * @param string $type
     * @return string
     */
    public function getClassName($type = '')
    {
        if ($this->className === null) {
            $repoClassName = $this->getRepositoryClassName(true);
            $baseName = str_replace('_', '', String::dashToCamelCase($repoClassName::$table));

            $this->className = $baseName;
        }

        return ucfirst($this->className . $type);
    }

    /**
     * Retourne le nom de la classe de test cible à partir du nom de la classe
     *
     * @param string $type
     * @return string
     */
    public function getTestClassName($type = '')
    {
        return $this->getClassName($type) . 'Test';
    }

    /**
     * Génération du code
     *
     * Alimente le tableau des fichiers à créer, écraser et ignorer
     */
    public function generate()
    {
        $this->addFile($this->getControllerFile());

        $this->addFile($this->getControllerViewFile('index'));
        $this->addFile($this->getControllerViewFile('view'));
        $this->addFile($this->getControllerViewFile('create-edit'));
        $this->addFile($this->getControllerViewFile('delete'));

        $this->addFile($this->getFormFile());
        $this->addFile($this->getSearchFormFile());
    }

    /**
     * Génération de la classe Controller
     *
     * @return File
     */
    protected function getControllerFile()
    {
        $moduleName = $this->getModuleName();
        $module = $this->moduleManager->getModule($moduleName);

        $moduleNamespace = $this->moduleManager->getModuleNamespace($moduleName);
        $moduleSrcDir = $module->getDir('src');

        $template = 'controller';
        $destination = $moduleSrcDir . '/Controller/' . $this->getClassName('Controller') . '.php';

        $repoClassName = $this->getRepositoryClassName(true);

        $content = $this->render($template, array(
            'crudName' => $this->getClassName(),

            'moduleName' => $moduleName,
            'controllerName' => String::camelCaseToDash($this->getClassName()),

            'namespace' => $moduleNamespace . '\Controller',
            'className' => $this->getClassName('Controller'),

            'repositoryFullClassName' => $this->getRepositoryClassName(true),
            'repositoryClassName' => $this->getRepositoryClassName(),

            'searchFormFullClassName' => $moduleNamespace . '\Form\\' . $this->getClassName('SearchForm'),
            'searchFormClassName' => $this->getClassName('SearchForm'),

            'formFullClassName' => $moduleNamespace . '\Form\\' . $this->getClassName('Form'),
            'formClassName' => $this->getClassName('Form'),

            'primary' => $repoClassName::$primary,
            'metadata' => $repoClassName::$metadata,
        ));

        return new File($destination, $content);
    }

    /**
     * Génération de la vue donnée pour la classe Controller
     *
     * @param string $view
     * @return File
     */
    protected function getControllerViewFile($view)
    {
        $moduleName = $this->getModuleName();
        $module = $this->moduleManager->getModule($moduleName);

        $controllerViewsDir = $module->getDir('views/' . String::camelCaseToDash($this->getClassName()));

        $template = 'views/' . $view;
        $destination = $controllerViewsDir . '/' . $view . '.phtml';

        $repoClassName = $this->getRepositoryClassName(true);
        $metadata = $repoClassName::$metadata;
        $primary = $repoClassName::$primary;

        $content = $this->render($template, array(
            'primary' => $primary,
            'columns' => $metadata,
        ));

        return new File($destination, $content);
    }

    /**
     * Génération de la classe Form
     *
     * @return File
     */
    protected function getFormFile()
    {
        $moduleName = $this->getModuleName();
        $module = $this->moduleManager->getModule($moduleName);

        $moduleNamespace = $this->moduleManager->getModuleNamespace($moduleName);
        $moduleSrcDir = $module->getDir('src');

        $template = 'form';
        $destination = $moduleSrcDir . '/Form/' . $this->getClassName('Form') . '.php';

        $repoClassName = $this->getRepositoryClassName(true);

        $tableDescription = $this->getTableDescription($repoClassName::$table);

        $content = $this->render($template, array(
            'namespace' => $moduleNamespace . '\Form',
            'className' => $this->getClassName('Form'),

            'formName' => $repoClassName::$table . '_form',

            'fields' => $tableDescription->formElements,
            'primary' => $repoClassName::$primary,
            
            'metadata' => $repoClassName::$metadata
        ));

        return new File($destination, $content);
    }

    /**
     * Génération de la classe SearchForm
     *
     * @return File
     */
    protected function getSearchFormFile()
    {
        $moduleName = $this->getModuleName();
        $module = $this->moduleManager->getModule($moduleName);

        $moduleNamespace = $this->moduleManager->getModuleNamespace($moduleName);
        $moduleSrcDir = $module->getDir('src');

        $template = 'searchform';
        $destination = $moduleSrcDir . '/Form/' . $this->getClassName('SearchForm') . '.php';

        $repoClassName = $this->getRepositoryClassName(true);

        $content = $this->render($template, array(
            'namespace' => $moduleNamespace . '\Form',
            'className' => $this->getClassName('SearchForm'),

            'formName' => $repoClassName::$table . '_searchform',

            'formClassName' => $this->getClassName('Form'),

            'primary' => $repoClassName::$primary,
        ));

        return new File($destination, $content);
    }

    /**
     * Retourne la description de la table
     *
     * @return array
     */
    protected function getTableDescription($tableName)
    {
        if ($this->tableDescription === null) {
            $this->tableDescription = new self::$tableDescClassName($this->db, $tableName);
        }

        return $this->tableDescription;
    }

}
