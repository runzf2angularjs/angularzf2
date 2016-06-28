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

namespace Oft\Test\View\Helper;

use Mockery;
use Oft\View\Helper\Assets;
use Oft\View\View;
use PHPUnit_Framework_TestCase;

class AssetsTest extends PHPUnit_Framework_TestCase
{
    
    protected $configuration = array(
        'options' => array(
            'cache_dir' => '/',
            'debug' => false,
            'version' => 1,
        ),
        'defaults' => array(
            '@bootstrap',
        ),
        'filters' => array(),
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
                        'files' => array(
                            'jquery/jquery.min.js',
                            'jquery/jquery.min.for-test.js',
                        ),
                    ),
                ),
            ),
            'no-files' => array(
                'module' => 'oft_ihm',
                'assets' => array(
                    array(
                        'type' => 'js',
                        'files' => array(
                            // No files
                        )
                    ),
                ),
            ),
        ),
    );
    
    protected function tearDown()
    {
        Assets::$collections = array();
        Assets::$added = array();
        Assets::$append = array();
        Assets::$prepend = array();
    }

    public function testSetConfiguration()
    {
        $helper = new Assets();
        $config = array('test');

        $return = $helper->setConfiguration($config);

        $this->assertInstanceOf('Oft\View\Helper\Assets', $return);
        $this->assertEquals($config, $helper->getConfiguration());
    }

    public function testGetCollectionUrl()
    {
        $view = new View();
        $helper = new Assets();
        $helper->setView($view);

        $baseUrl = '/my/app';
        $version = 1;
        $name = 'name';
        $type = 'type';
        $hash = 'hash';
        $index = 1;

        $view->setBaseUrl($baseUrl);

        $expected = $baseUrl .
            '/assets' .
            '/v' . $version .
            '/' . $name .
            '/' . $type .
            '/' . $hash . $index .
            '.' . $type;

        $actual = $helper->getCollectionUrl($version, $name, $type, $hash, $index);

        $this->assertEquals($expected, $actual);
    }

    public function testGetCollectionHash()
    {
        $helper = new Assets();

        $files = array(
            '/my/first/file',
            '/my/sec/file',
        );

        $expected = substr(md5(implode(';', $files)), 0, 7);
        $actual = $helper->getCollectionHash($files);

        $this->assertEquals($expected, $actual);
    }

    public function testGetVersion()
    {
        $helper = new Assets();

        $helper->setConfiguration($this->configuration);

        $expected = $this->configuration['options']['version'];
        $actual = $helper->getVersion();

        $this->assertEquals($expected, $actual);
    }

    public function testFile()
    {
        $view = new View();
        $helper = new Assets();
        $helper->setView($view);

        $baseUrl = '/my/app/';
        $version = $this->configuration['options']['version'];
        $pathWithSlash = '/path/to/asset';
        $pathWithoutSlash = 'path/to/asset';

        $helper->setConfiguration($this->configuration);
        $view->setBaseUrl($baseUrl);

        $expected = rtrim($baseUrl, '/') .
            '/assets/v' .
            $version .
            '/' .
            ltrim($pathWithSlash, '/');

        $this->assertEquals($expected, $helper->file($pathWithSlash));
        $this->assertEquals($expected, $helper->file($pathWithoutSlash));
    }

    public function testAddNoReference()
    {
        $view = new View();
        $helper = new Assets();
        $helper->setView($view);

        $helper->setConfiguration($this->configuration);
        $view->setBaseUrl('/');

        $helper->add('jquery');

        $this->assertCount(1, Assets::$collections); // 1 collection added
    }

    public function testAddEmptyFiles()
    {
        $helper = new Assets();

        $helper->setConfiguration($this->configuration);

        $helper->add('no-files');

        $this->assertCount(0, Assets::$collections); // 0 : no collection added
    }

    public function testAddViaInvoke()
    {
        $view = new View();
        $helper = new Assets();
        $helper->setView($view);

        $helper->setConfiguration($this->configuration);
        $view->setBaseUrl('/');

        $helper('jquery');

        $this->assertCount(1, Assets::$collections); // 1 collection added
    }

    public function testAddNoDouble()
    {
        $view = new View();
        $helper = new Assets();
        $helper->setView($view);

        $helper->setConfiguration($this->configuration);
        $view->setBaseUrl('/');

        $helper->add('jquery')->add('jquery');

        $this->assertCount(1, Assets::$collections); // 1 collection added
    }

    public function testAddNoLoop()
    {
        $view = new View();
        $helper = new Assets();
        $helper->setView($view);

        $this->configuration['collections'] = array(
            'ab1' => array(
                'module' => 'test',
                'assets' => array(
                    '@ab2',
                    array(
                        'type' => 'js',
                        'files' => array(
                            'ab1.js',
                        ),
                    ),
                ),
            ),
            'ab2' => array(
                'module' => 'test',
                'assets' => array(
                    '@ab1',
                    array(
                        'type' => 'js',
                        'files' => array(
                            'ab2.js',
                        ),
                    ),
                ),
            ),
        );
        
        $helper->setConfiguration($this->configuration);
        $view->setBaseUrl('/');

        $helper->add('ab1');

        $this->assertCount(2, Assets::$added);
        $this->assertCount(2, Assets::$collections);
    }

    public function testAddWithReference()
    {
        $view = new View();
        $helper = new Assets();
        $helper->setView($view);

        $helper->setConfiguration($this->configuration);
        $view->setBaseUrl('/');
        
        $helper->add('bootstrap');

        $this->assertCount(3, Assets::$collections); // 1+2 collections added
    }

    public function testFinalizeCss()
    {
        $helper = new Assets();
        
        $configuration = array(
            'defaults' => array(),
        );

        $helper->setConfiguration($configuration);

        $collections = array(
            'jquery.css' => array(
                'url' => '/url/to/jquery/css',
                'type' => 'css',
                'tag' => array(
                    'media' => 'media-test',
                    'conditional' => 'cond-test',
                    'extras' => array(
                        'k' => 'v',
                    ),
                )
            ),
        );

        // Forcer les collections
        Assets::$collections = $collections;

        $view = Mockery::mock('Zend\View\Renderer\RendererInterface');

        $headLink = Mockery::mock('Zend\View\Helper\HeadLink');
        $headLink->shouldReceive('appendStylesheet')
            ->once()
            ->withArgs(array(
                $collections['jquery.css']['url'],
                $collections['jquery.css']['tag']['media'],
                $collections['jquery.css']['tag']['conditional'],
                $collections['jquery.css']['tag']['extras'],
            ))
            ->andReturnSelf();

        $view->shouldReceive('headLink')
            ->once()->withNoArgs()
            ->andReturn($headLink);

        $helper->setView($view);

        $helper->finalize();
    }

    public function testFinalizeJs()
    {
        $helper = new Assets();

        $configuration = array(
            'defaults' => array(),
        );

        $helper->setConfiguration($configuration);

        $collections = array(
            'jquery.js' => array(
                'url' => '/url/to/jquery/js',
                'type' => 'js',
                'tag' => array(
                    'type' => 'type/test',
                    'attrs' => array(
                        'k' => 'v',
                    ),
                )
            ),
        );

        // Forcer les collections
        Assets::$collections = $collections;

        $view = Mockery::mock('Zend\View\Renderer\RendererInterface');

        $headScript = Mockery::mock('Zend\View\Helper\HeadScript');
        $headScript->shouldReceive('appendFile')
            ->once()
            ->withArgs(array(
                $collections['jquery.js']['url'],
                $collections['jquery.js']['tag']['type'],
                $collections['jquery.js']['tag']['attrs'],
            ))
            ->andReturnSelf();

        $view->shouldReceive('headScript')
            ->once()->withNoArgs()
            ->andReturn($headScript);

        $helper->setView($view);

        $helper->finalize();
    }

    public function testAddDefaults()
    {
        $this->configuration['defaults'] = array('@jquery');

        $view = new View();
        $helper = new Assets();
        $helper->setView($view);

        $helper->setConfiguration($this->configuration);
        $view->setBaseUrl('/');

        $helper->addDefaults();

        $this->assertCount(1, Assets::$collections); // 1 collection added
    }
    
}
