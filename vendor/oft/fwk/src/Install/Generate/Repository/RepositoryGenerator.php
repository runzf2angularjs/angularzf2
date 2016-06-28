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

namespace Oft\Install\Generate\Repository;

use Doctrine\DBAL\Connection;
use DomainException;
use InvalidArgumentException;
use Oft\Install\Generate\File;
use Oft\Install\Generate\GeneratorAbstract;
use Oft\Install\Tools\MySql\TableDescription;
use Oft\Module\ModuleManager;
use Oft\Util\String;

class RepositoryGenerator extends GeneratorAbstract
{

    /**
     * Classe de description de la table
     *
     * @var string
     */
    public static $tableDescClassName = 'Oft\Install\Tools\MySql\TableDescription';

    /**
     * Nom de la table ciblée
     *
     * @var string
     */
    public $tableName;

    /**
     * Nom du module de destination
     *
     * @var string
     */
    public $moduleName;

    /**
     * Nom de la classe repository générée
     *
     * @var string
     */
    public $className;

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
     * Retourne le nom de la table cible
     *
     * @return string
     */
    public function getTableName()
    {
        if ($this->tableName === null) {
            throw new InvalidArgumentException('Le nom de la table cible n\'a pas été précisé');
        }

        return $this->tableName;
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

        if (!in_array($this->moduleName, $this->modules)) {
            throw new DomainException('Le module ' . $this->moduleName . ' n\'existe pas');
        }

        return $this->moduleName;
    }

    /**
     * Retourne le nom de la classe cible
     * Construit le nom à partir du nom de la table si la valeur est absente
     *
     * @var bool $base Classe de base
     * @return string
     */
    public function getClassName($base = false)
    {
        if ($this->className === null) {
            $this->className = str_replace('_', '', String::dashToCamelCase($this->getTableName()));
        }

        return $this->className . ($base ? 'Base' : '') . 'Repository';
    }

    /**
     * Retourne le nom de la classe de test cible
     *
     * @return string
     */
    public function getTestClassName()
    {
        return $this->getClassName() . 'Test';
    }

    /**
     * Génération du code
     *
     * Alimente le tableau des fichiers à créer, écraser et ignorer
     */
    public function generate()
    {
        $this->addFile($this->getBaseRepositoryFile());
        $this->addFile($this->getRepositoryFile());
        $this->addFile($this->getRepositoryTestFile());
    }

    /**
     * Génération de la classe de base du repository
     *
     * Cette classe n'est pas sauvegardée lorsqu'elle est regénérée
     *
     * @return File
     */
    protected function getBaseRepositoryFile()
    {
        $description = $this->getTableDescription($this->getTableName());

        $moduleName = $this->getModuleName();
        $module = $this->moduleManager->getModule($moduleName);
        
        $moduleNamespace = $this->moduleManager->getModuleNamespace($moduleName);
        $moduleSrcDir = $module->getDir('src');

        $template = 'baseRepository';
        $destination = $moduleSrcDir . '/Repository/Base/' . $this->getClassName(true) . '.php';

        $content = $this->render($template, array(
            'tableName' => $this->getTableName(),
            'namespace' => $moduleNamespace . '\Repository\Base',
            'className' => $this->getClassName(true),

            'description' => $description,
            'columns' => array_keys($description->columns),
            'primary' => $description->primary,
        ));

        return new File($destination, $content, false);
    }

    /**
     * Génération de la classe du repository
     *
     * Cette classe n'est pas regénérée lorsqu'elle existe déjà
     *
     * @return File
     */
    protected function getRepositoryFile()
    {
        $moduleName = $this->getModuleName();
        $module = $this->moduleManager->getModule($moduleName);

        $moduleNamespace = $this->moduleManager->getModuleNamespace($moduleName);
        $moduleSrcDir = $module->getDir('src');

        $template = 'repository';
        $destination = $moduleSrcDir . '/Repository/' . $this->getClassName() . '.php';

        $content = $this->render($template, array(
            'namespace' => $moduleNamespace . '\Repository',
            'className' => $this->getClassName(),
            
            'baseClassName' => $this->getClassName(true),
            'baseFullClassName' => $moduleNamespace . '\Repository\Base\\' . $this->getClassName(true),
        ));

        return new File($destination, $content, true, false);
    }

    /**
     * Génération de la classe RepositoryTest
     *
     * @return File
     */
    protected function getRepositoryTestFile()
    {
        $description = $this->getTableDescription($this->getTableName());

        $moduleName = $this->getModuleName();
        $module = $this->moduleManager->getModule($moduleName);

        $moduleNamespace = $this->moduleManager->getModuleNamespace($moduleName);
        $moduleSrcDir = $module->getDir('tests/src');

        $template = 'repositoryTest';
        $destination = $moduleSrcDir . '/Repository/' . $this->getTestClassName() . '.php';

        $content = $this->render($template, array(
            'tableName' => $this->getTableName(),
            'namespace' => $moduleNamespace . '\Test\Repository',
            'testClassName' => $this->getTestClassName(),
            'className' => $this->getClassName(),
            'fullClassName' => $moduleNamespace . '\Repository\\' . $this->getClassName(),
            'columns' => array_keys($description->columns),
            'primary' => $description->primary,
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
