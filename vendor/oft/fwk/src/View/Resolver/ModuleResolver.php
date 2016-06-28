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

namespace Oft\View\Resolver;

use Oft\Module\ModuleManager;
use Zend\View\Renderer\RendererInterface;
use Zend\View\Resolver\ResolverInterface;

class ModuleResolver implements ResolverInterface
{

    /**
     * Gestionnaire de modules
     *
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * Suffixe des fichiers vue
     *
     * @var string
     */
    protected $suffix;

    /**
     * Intialisation du composant de résolution de la vue
     *
     * @param ModuleManager $moduleManager
     * @param string $suffix
     */
    public function __construct(ModuleManager $moduleManager, $suffix = 'phtml')
    {
        $this->moduleManager = $moduleManager;
        $this->suffix = '.' . ltrim($suffix, '.');
    }

    /**
     * Retourne le chemin vers le fichier vue ciblé
     *
     * @param string $name
     * @param RendererInterface $renderer
     * @throws \RuntimeException
     * @return string
     */
    public function resolve($name, RendererInterface $renderer = null)
    {
        $parts = preg_split('|/|', $name);
        if (!$parts || count($parts)<2) {
            throw new \RuntimeException("No module specified");
        }

        $module = array_shift($parts);
        if ($module === '@default') {
            $module = $this->moduleManager->getDefault();
        }
        $viewDir = $this->moduleManager->getModule($module)->getDir('views');
        $template = implode('/', $parts);

        return rtrim($viewDir, '\\/') . '/' . $template . $this->suffix;
    }

}
