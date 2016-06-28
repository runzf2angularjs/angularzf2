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

use Mockery;
use Oft\Asset\AssetManager;
use Oft\Module\ModuleManager;
use PHPUnit_Framework_TestCase;

class AssetManagerTest extends PHPUnit_Framework_TestCase
{
    
    protected $configuration = array(
        'options' => array(
            'cache_dir' => '/',
            'debug' => true,
            'version' => 1,
        ),
        'filters' => array(
            'CssMinFilter' => array(
                'class' => 'Assetic\Filter\CssMinFilter',
                'args' => array(),
            ),
        ),
        'collections' => array(
            'bootstrap' => array(
                'module' => 'oft_ihm',
                'assets' => array(
                    '@html5.shiv',
                    array(
                        'type' => 'js',
                        'files' => array(
                            'bootstrap/js/bootstrap.min.js',
                            'bootstrap/js/bootstrap.min.test.js',
                        ),
                    ),
                    array(
                        'type' => 'css',
                        'files' => array(
                            'bootstrap/css/bootstrap.min.css',
                            'bootstrap/css/bootstrap-theme.min.css',
                        ),
                        'filters' => array(
                            'CssMinFilter', 'CssRewriteFilter'
                        )
                    ),
                ),
            ),
            'html5.shiv' => array(
                'module' => 'oft_ihm',
                'assets' => array(
                    array(
                        'type' => 'js',
                        'tag' => array(
                            'type' => 'text/javascript',
                            'attrs' => array('conditional' => 'lt IE 9'),
                        ),
                        'files' => array(
                            'html5/html5shiv.min.js',
                            'html5/respond.min.js',
                        ),
                    ),
                ),
            ),
            'jquery' => array(
                'module' => 'oft_ihm',
                'assets' => array(
                    array(
                        'type' => 'js',
                        'filters' => array(),
                        'files' => array(
                            'jquery/jquery.min.js',
                            'jquery/jquery.min.for-test.js',
                        ),
                        'vars' => array(
                            'v1k' => 'v1v',
                            'v2k' => 'v2v',
                        )
                    ),
                ),
            ),
        ),
    );

    public function testGetCache()
    {
        $moduleManager = new ModuleManager();
        $assetManager = new AssetManager($moduleManager, $this->configuration);

        $cache = $assetManager->getCache();

        $this->assertInstanceOf('Assetic\Cache\FilesystemCache', $cache);
    }

    public function testGetFilterManager()
    {
        $moduleManager = new ModuleManager();
        $assetManager = new AssetManager($moduleManager, $this->configuration);

        $this->assertInstanceOf('Assetic\FilterManager', $assetManager->getFilterManager());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Le filtre Assetic\Filter\NotAnExistingFilter n'existe pas
     */
    public function testGetFilterManagerException()
    {
        $config = $this->configuration;
        $config['filters']['KoFilter'] = array(
            'class' => 'Assetic\Filter\NotAnExistingFilter',
            'args' => array(),
        );

        $moduleManager = new ModuleManager();
        $assetManager = new AssetManager($moduleManager, $config);

        $this->assertInstanceOf('Assetic\FilterManager', $assetManager->getFilterManager());
    }

    public function testGetAssetFactory()
    {
        $config = $this->configuration;
        $config['filters'] = array(); // Aucun filtre
        
        $root = '/';

        $moduleManager = new ModuleManager();
        $assetManager = new AssetManager($moduleManager, $config);

        $assetFactory = $assetManager->getAssetFactory($root);

        $this->assertInstanceOf('Assetic\Factory\AssetFactory', $assetFactory);
    }

    public function testGetAssetWriter()
    {
        $config = $this->configuration;
        $config['filters'] = array(); // Aucun filtre
        
        $webRoot = '/';

        $moduleManager = new ModuleManager();
        $assetManager = new AssetManager($moduleManager, $config);

        $assetWriter = $assetManager->getAssetWriter($webRoot);

        $this->assertInstanceOf('Assetic\AssetWriter', $assetWriter);
    }

    public function testGetCollectionModule()
    {
        $moduleManager = new ModuleManager();
        $assetManager = new AssetManager($moduleManager, $this->configuration);

        $result = $assetManager->getCollectionModule('bootstrap');

        $this->assertEquals($this->configuration['collections']['bootstrap']['module'], $result);
    }

    public function testGetCollectionConfig()
    {
        $moduleManager = new ModuleManager();
        $assetManager = new AssetManager($moduleManager, $this->configuration);

        $result = $assetManager->getCollectionConfig('bootstrap');

        $this->assertEquals($this->configuration['collections']['bootstrap'], $result);
    }

    public function testGetCollectionFiles()
    {
        $moduleManager = new ModuleManager();
        $assetManager = new AssetManager($moduleManager, $this->configuration);

        $result = $assetManager->getCollectionFiles('jquery', 0);
        $expected = $this->configuration['collections']['jquery']['assets'][0]['files'];

        $this->assertEquals($expected, $result);
    }

    public function testGetCollectionFilters()
    {
        $moduleManager = new ModuleManager();
        $assetManager = new AssetManager($moduleManager, $this->configuration);

        $expected = array('CssMinFilter', 'CssRewriteFilter');
        $actual = $assetManager->getCollectionFilters('bootstrap', 2);

        foreach ($actual as $key => $name) {
            $this->assertEquals($expected[$key], $name);
        }
    }

    public function testGetCollectionVarsNotAnArray()
    {
        $config = $this->configuration;
        $config['filters'] = array(); // Aucun filtre
        $config['collections'] = array( // Collections spécifiques
            'jquery' => array(
                'module' => 'oft_ihm',
                'assets' => array(
                    'no-vars'
                ),
            ),
        );

        $moduleManager = new ModuleManager();
        $assetManager = new AssetManager($moduleManager, $config);

        $expected = array();
        $actual = $assetManager->getCollectionVars('jquery', 0);

        $this->assertEquals($expected, $actual);
    }

    public function testGetCollectionVars()
    {
        $moduleManager = new ModuleManager();
        $assetManager = new AssetManager($moduleManager, $this->configuration);

        $expected = array(
            'v1k' => 'v1v',
            'v2k' => 'v2v',
        );
        $actual = $assetManager->getCollectionVars('jquery', 0);

        $this->assertEquals($expected, $actual);
    }
    
    public function testGetCollectionNotInDebugWithCache()
    {
        $config = $this->configuration;
        $config['options']['debug'] = false; // Pas en mode debug = no cache
        $config['filters'] = array(); // Aucun filtre
        $config['collections'] = array( // Collection simple spécifique
            'jquery' => array(
                'module' => 'oft_ihm',
                'assets' => array(
                    array(
                        'type' => 'js',
                        'files' => array(
                            'jquery/jquery.js',
                        ),
                    ),
                ),
            ),
        );

        $module = Mockery::mock('\Oft\Module\ModuleInterface');
        $module->shouldReceive('getDir')
            ->once()
            ->with('assets')
            ->andReturn('/');

        $moduleManager = Mockery::mock('\Oft\Module\ModuleManager');
        $moduleManager->shouldReceive('getModule')
            ->once()
            ->withArgs(array('oft_ihm'))
            ->andReturn($module);
        
        $assetManager = new AssetManager($moduleManager, $config);

        $collection = $assetManager->getCollection('jquery', 0);

        $this->assertInstanceOf('Assetic\Asset\AssetCache', $collection);
    }

    public function testGetCollectionInDebugNoCache()
    {
        $config = $this->configuration;
        $config['options']['debug'] = true; // En mode debug = cache
        $config['filters'] = array(); // Aucun filtre
        $config['collections'] = array( // Collection simple spécifique
            'jquery' => array(
                'module' => 'oft_ihm',
                'assets' => array(
                    array(
                        'type' => 'js',
                        'files' => array(
                            'jquery/jquery.js',
                        ),
                    ),
                ),
            ),
        );

        $module = Mockery::mock('\Oft\Module\ModuleInterface');
        $module->shouldReceive('getDir')
            ->once()
            ->with('assets')
            ->andReturn('/');

        $moduleManager = Mockery::mock('\Oft\Module\ModuleManager');
        $moduleManager->shouldReceive('getModule')
            ->once()
            ->with('oft_ihm')
            ->andReturn($module);

        $assetManager = new AssetManager($moduleManager, $config);

        $collection = $assetManager->getCollection('jquery', 0);

        $this->assertInstanceOf('Assetic\Asset\AssetCollection', $collection);
    }

}