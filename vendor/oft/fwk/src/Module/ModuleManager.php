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

namespace Oft\Module;

use Oft\Module\ModuleInterface;
use Oft\Mvc\Application;
use Oft\Util\Arrays;

class ModuleManager
{

    /**
     * Modules chargés
     *
     * @var array
     */
    protected $modules = array();

    /**
     * Configuration issue des modules
     *
     * @var array
     */
    protected $modulesConfig;

    /**
     * Modules initialisés
     *
     * @var array
     */
    protected $initializedModules = array();

    /**
     * Namespace de chaque modules
     * 
     * @var array
     */
    protected $modulesNamespaces = array();

    /**
     * Module par défaut
     *
     * @var string
     */
    protected $defaultModule;

    /**
     * Ajoute le(s) module(s) donnés
     *
     * @param array $modules Modules à ajouter
     * @return void
     */
    public function addModules(array $modules)
    {
        foreach ($modules as $moduleName) {
            $this->addModule($moduleName);
        }

        return $this;
    }

    /**
     * Instancie la classe Module du module donné
     *
     * @param string $module Nom du module
     * @throws \RuntimeException
     * @throws \RuntimeException
     * @return void
     */
    public function addModule($module, $default = false)
    {
        if (is_string($module)) {
            $moduleClass = $module . '\\Module';
            $moduleInstance = new $moduleClass;
        } else {
            $moduleClass = get_class($module);
            $moduleInstance = $module;
        }

        if (!$moduleInstance instanceof ModuleInterface) {
            throw new \RuntimeException("Module instance of '$module' does not implement Oft\ModuleInterface");
        }

        $moduleName = $moduleInstance->getName();

        if (array_key_exists($moduleName, $this->modules)) {
            throw new \RuntimeException("Can't add a module instance with the same name as another registered module");
        }

        $this->modulesConfig = null;
        $this->modules[$moduleName] = $moduleInstance;

        $pos = strrpos($moduleClass, '\\');
        if ($pos === false) {
            $this->modulesNamespaces[$moduleName] = '';
        } else {
            $this->modulesNamespaces[$moduleName] = substr($moduleClass, 0, $pos);
        }

        if ($default) {
            $this->defaultModule = $moduleInstance->getName();
        }

        return $this;
    }

    public function getDefault()
    {
        return $this->defaultModule;
    }

    /**
     * Lance la méthode init() des modules chargés
     *
     * @param Application $app
     * @return void
     */
    public function init(Application $app)
    {
        foreach ($this->modules as $moduleName => $moduleInstance) {
            // Unique initialisation de chaque module
            if (in_array($moduleName, $this->initializedModules)) {
                continue;
            }

            $moduleInstance->init($app);
            $this->initializedModules[] = $moduleName;
        }

        return $this;
    }

    /**
     * Retourne la configuration fusionnées issue des modules
     *
     * @param bool $cli Mode CLI
     * @return array
     */
    public function getModulesConfig($cli)
    {
        $isCli = (bool) $cli;
        if ($this->modulesConfig === null) {
            $moduleConfig = array();
            foreach ($this->modules as $moduleName => $moduleInstance) {
                if ($moduleName == $this->defaultModule) {
                    continue; // Skip default, should be the last one
                }
                $moduleConfig = Arrays::mergeConfig($moduleConfig, $moduleInstance->getConfig($isCli));
            }

            // Default module is merged last
            if ($this->defaultModule !== null) {
                $moduleConfig = Arrays::mergeConfig(
                    $moduleConfig,
                    $this->modules[$this->defaultModule]->getConfig($isCli)
                );
            }

            $this->modulesConfig = $moduleConfig;
        }

        return $this->modulesConfig;
    }

    /**
     * Retourne les instances des modules chargés
     *
     * @return array
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * Retourne l'instance d'un module donné
     *
     * @param string $name
     * @return ModuleInterface
     */
    public function getModule($name)
    {
        return $this->modules[$name];
    }

    /**
     * Retourne le namespace d'un module donné
     * 
     * @param string $name
     * @return string
     */
    public function getModuleNamespace($name)
    {
        return $this->modulesNamespaces[$name];
    }
}
