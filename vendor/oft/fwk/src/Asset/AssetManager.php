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

namespace Oft\Asset;

use Assetic\Asset\AssetCache;
use Assetic\Asset\AssetCollection;
use Assetic\AssetWriter;
use Assetic\Cache\CacheInterface;
use Assetic\Cache\FilesystemCache;
use Assetic\Factory\AssetFactory;
use Assetic\FilterManager;
use Oft\Module\ModuleManager;

/**
 * Composant de gestion des Assets
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class AssetManager
{
    /**
     * Assetic configuration
     *
     * @var array
     */
    protected $configuration = array();

    /**
     * Assetic filters
     *
     * @var array
     */
    protected $filters = array();

    /**
     * Mode debug
     *
     * @var bool
     */
    protected $isDebug = false;

    /**
     * Cache
     *
     * @var CacheInterface
     */
    protected $cache;

    /**
     * Asset Factories, une factory par module
     *
     * @var array
     */
    protected $assetFactory = array();

    /**
     * Asset Writer
     *
     * @var AssetWriter
     */
    protected $assetWriter;

    /**
     * Filter Manager
     *
     * @var FilterManager
     */
    protected $filterManager;

    /**
     * Module Manager
     *
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     *
     * @param ModuleManager $moduleManager
     * @param array $config
     */
    public function __construct(ModuleManager $moduleManager, array $config)
    {
        $this->moduleManager = $moduleManager;
        $this->configuration = $config;

        $this->isDebug = (bool)$config['options']['debug'];
    }

    /**
     * Retourne l'objet de gestion du cache
     *
     * @return CacheInterface
     */
    public function getCache()
    {
        if (!$this->cache) {
            $cacheDir = $this->configuration['options']['cache_dir'];
            $this->cache = new FilesystemCache($cacheDir);
        }

        return $this->cache;
    }

    /**
     * Retourne la configuration
     *
     * @return array
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Retourne l'instance du ModuleManager
     *
     * @return ModuleManager
     */
    public function getModuleManager()
    {
        return $this->moduleManager;
    }

    /**
     * Retourne l'instance de l'AssetFactory
     *
     * @param string $root Emplacement des fichiers
     * @return AssetFactory
     */
    public function getAssetFactory($root)
    {
        if (!isset($this->assetFactory[$root])) {
            $factory = new AssetFactory($root, $this->isDebug);
            $factory->setFilterManager($this->getFilterManager());
            $this->assetFactory[$root] = $factory;
        }

        return $this->assetFactory[$root];
    }

    /**
     * Retourne l'instance de l'AssetWriter
     *
     * @param string $webRoot
     * @return AssetWriter
     */
    public function getAssetWriter($webRoot)
    {
        if ($this->assetWriter === null) {
            $this->assetWriter = new AssetWriter($webRoot);
        }

        return $this->assetWriter;
    }

    /**
     * Retourne l'instance du FilterManager
     *
     * @return FilterManager
     */
    public function getFilterManager()
    {
        if ($this->filterManager === null) {
            $filterManager = new FilterManager();
            $filters = $this->configuration['filters'];

            foreach ($filters as $alias => $definition) {
                if (!\class_exists($definition['class'])) {
                    throw new \RuntimeException('Le filtre ' . $definition['class'] . ' n\'existe pas');
                }

                $filterClass = $definition['class'];
                $filter = new $filterClass($definition['args']);
                $filterManager->set($alias, $filter);
            }

            $this->filterManager = $filterManager;
        }

        return $this->filterManager;
    }

    /**
     * Crée une collection (ensemble de sous-collections)
     *
     * @param string $name Nom de la collection
     * @param string $index Index de la collection
     * @return AssetCollection
     */
    public function getCollection($name, $index)
    {
        $files = $this->getCollectionFiles($name, $index);
        $filters = $this->getCollectionFilters($name, $index);
        $options = array(
            'vars' => $this->getCollectionVars($name, $index)
        );

        $moduleName = $this->getCollectionModule($name);
        $root = $this->moduleManager
            ->getModule($moduleName)
            ->getDir('assets');

        $factory = $this->getAssetFactory($root);
        $collection = $factory->createAsset($files, $filters, $options);

        // Cache
        if (!$this->isDebug) {
            $collection = new AssetCache($collection, $this->getCache());
        }

        return $collection;
    }

    /**
     * Retourne le module de la collection donnée
     *
     * @param string $name Nom de la collection
     * @return string
     */
    public function getCollectionModule($name)
    {
        return $this->configuration['collections'][$name]['module'];
    }

    /**
     * Retourne la configuration d'un ensemble de collections
     *
     * @param string $name Nom de la collection
     * @return array
     */
    public function getCollectionConfig($name)
    {
        return $this->configuration['collections'][$name];
    }

    /**
     * Retourne les fichiers d'une collection donnée
     *
     * @param string $name Nom de la collection
     * @param string $index Index de la collection
     * @return array
     */
    public function getCollectionFiles($name, $index)
    {
        $config = $this->getCollectionConfig($name);
        $assets = $config['assets'][$index];

        $files = array();
        if (isset($assets['files'])) {
            $files = $assets['files'];
        }

        return $files;
    }

    /**
     * Retourne les filtres d'une collection donnée
     *
     * @param string $name Nom de la collection
     * @param string $index Index de la collection
     * @return array
     */
    public function getCollectionFilters($name, $index)
    {
        $config = $this->getCollectionConfig($name);
        $assets = $config['assets'][$index];

        $filters = array();
        if (isset($assets['filters'])) {
            $filters = $assets['filters'];
        }

        return $filters;
    }

    /**
     * Retourne les filtres d'une collection donnée
     *
     * @param string $name Nom de la collection
     * @param string $index Index de la collection
     * @return array
     */
    public function getCollectionVars($name, $index)
    {
        $config = $this->getCollectionConfig($name);
        $assets = $config['assets'][$index];

        $var = array();
        if (isset($assets['vars'])) {
            $var = $assets['vars'];
        }

        return $var;
    }

}
