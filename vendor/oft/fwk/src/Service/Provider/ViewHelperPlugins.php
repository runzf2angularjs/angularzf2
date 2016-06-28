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

namespace Oft\Service\Provider;

use Oft\Service\FactoryInterface;
use Oft\Service\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\View\HelperPluginManager;

class ViewHelperPlugins implements FactoryInterface
{
    /**
     *
     * @var HelperPluginManager 
     */
    protected $viewHelperPlugin;

    public function setViewHelperPlugin(ServiceLocatorAwareInterface $viewHelperPlugin)
    {
        $this->viewHelperPlugin = $viewHelperPlugin;

        return $this;
    }
    
    /**
     * 
     * @return HelperPluginManager
     */
    public function getViewHelperPlugin()
    {
        if ($this->viewHelperPlugin === null) {
            $this->viewHelperPlugin = new HelperPluginManager();
        }

        return $this->viewHelperPlugin;
    }

    public function create(ServiceLocatorInterface $app)
    {
        $viewConfig = $app->config['view'];

        $viewHelperPlugins = $this->getViewHelperPlugin();
        
        $viewHelperPlugins->setServiceLocator($app);

        foreach ($viewConfig['helpers'] as $name => $service) {
            $viewHelperPlugins->setInvokableClass($name, $service);
        }

        foreach ($viewConfig['helpersFactories'] as $name => $service) {
            $viewHelperPlugins->setFactory($name, $service);
        }

        foreach ($viewConfig['helpersConfig'] as $helperConfigClass) {
            $helperConfig = new $helperConfigClass();
            $helperConfig->configureServiceManager($viewHelperPlugins);
        }
        
        // Set configuration to AssetManager viewhelper
        $viewHelperPlugins->get('assets')
            ->setConfiguration($app->config['assets']);

        // Set title
        $viewHelperPlugins->get('title')
            ->setAppName($app->config['application']['name']);

        // Footer
        $viewHelperPlugins->get('footer')
            ->setAppName($app->config['application']['name'])
            ->setContact($app->config['application']['contact'])
            ->setLinks($app->config['footer-links']);

        return $viewHelperPlugins;
    }

}
