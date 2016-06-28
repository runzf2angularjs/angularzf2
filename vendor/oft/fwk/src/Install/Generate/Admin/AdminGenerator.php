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

namespace Oft\Install\Generate\Admin;

use Doctrine\DBAL\Connection;
use DomainException;
use InvalidArgumentException;
use Oft\Install\Generate\File;
use Oft\Install\Generate\GeneratorAbstract;
use Oft\Module\ModuleManager;
use Oft\Util\String;
use Oft\Validator\Cuid;
use Oft\View\View;
use Zend\Validator\StringLength;

class AdminGenerator extends GeneratorAbstract
{

    /**
     * Nom de l'utilsateur
     *
     * @var string
     */
    public $username;

    /**
     * Mot de passe
     *
     * @var string
     */
    public $password;

    /**
     * Nom du module
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
     * @param View $view
     * @param Connection $db
     * @param ModuleManager $moduleManager
     */
    public function __construct(ModuleManager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
        $this->modules = array_keys($moduleManager->getModules());
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
     * Génération du code
     *
     * Alimente le tableau des fichiers à créer, écraser et ignorer
     */
    public function generate()
    {
        $this->addFile($this->getMigrationFile());
    }

    /**
     * Retourne le nom d'utilisateur saisi validé
     * 
     * @return string
     * @throws InvalidArgumentException
     */
    public function getUsername()
    {
        $validatorCuid = new Cuid();

        if (!$validatorCuid->isValid($this->username)) {
            $msg = $validatorCuid->getMessages();
            throw new InvalidArgumentException(array_pop($msg));
        }

        return $this->username;
    }

    /**
     * Retourne le mot de passe saisi validé
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function getPassword()
    {
        $validatorPassword = new StringLength(array('min' => 8));

        if (!$validatorPassword->isValid($this->password)) {
            $msg = "Le mot de passe doit être supérieur à 8 caractères";
            throw new InvalidArgumentException($msg);
        }

        return $this->password;
    }

    /**
     * Génération de la classe migration
     *
     * @return File
     */
    protected function getMigrationFile()
    {
        $username = $this->getUsername();

        $moduleName = $this->getModuleName();
        $module = $this->moduleManager->getModule($moduleName);

        $moduleNamespace = $this->moduleManager->getModuleNamespace($moduleName);
        $moduleSqlDir = $module->getDir('sql');

        $className = 'Version_' . String::stringToValidClassName($username) . '_' . date('YmdHis');
        $salt = dechex(mt_rand());

        $template = 'migration';
        $destination = $moduleSqlDir . '/' . $className . '.php';
        
        $content = $this->render($template, array(
            'namespace' => $moduleNamespace . '\Sql',
            'className' => $className,
            'username' => $username,
            'password' => md5($salt . $this->getPassword()),
            'salt' => $salt,
        ));

        return new File($destination, $content);
    }

}
