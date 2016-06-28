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

use Zend\View\Renderer\RendererInterface;
use Zend\View\Resolver\ResolverInterface;

class DirectResolver implements ResolverInterface
{

    /**
     * Préfixe du fichier vue
     *
     * Ici, le chemin vers le fichier
     *
     * @var string
     */
    protected $prefix;

    /**
     * Suffixe du fichier vue
     *
     * Ici, l'extension du fichier
     *
     * @var string
     */
    protected $suffix;

    /**
     * Initialisation
     *
     * @param string $prefix
     * @param string $suffix
     * @return self
     */
    public function __construct($prefix, $suffix = 'phtml')
    {
        $this->prefix = rtrim($prefix, '/\\') . DIRECTORY_SEPARATOR;
        $this->suffix = '.' . ltrim($suffix, '.');
    }

    /**
     * Résolution du chemin complet vers le fichier vue
     *
     * @param string $name Nom de la vue
     * @param RendererInterface $renderer
     * @return string
     */
    public function resolve($name, RendererInterface $renderer = null)
    {
        return $this->prefix . $name . $this->suffix;
    }

}
