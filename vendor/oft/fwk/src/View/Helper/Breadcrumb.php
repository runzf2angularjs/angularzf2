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

namespace Oft\View\Helper;

class Breadcrumb extends PluginStandaloneTranslator
{
    
    /**
     * Initialise la structure HTML
     *
     * @return void
     */
    public function init()
    {
        $this->getContainer()
            ->setPrefix('<ol class="breadcrumb"><span class="glyphicon glyphicon-map-marker"></span>&nbsp;')
            ->setPostfix('</ol>');
    }

    /**
     * Ajoute un item au fil d'ariane
     *
     * @param string $text Texte de l'item
     * @param string $link Lien facultatif sur l'item
     * @return self
     */
    public function __invoke($text = null, $link = null)
    {
        $placeholder = $this->getContainer();
        if ($text !== null) {
            $text = e($this->translator->translate($text));
            
            if ($link) {
                $text = '<a href="' . e($link) . '">' . $text . '</a>';
            }
            $placeholder->append('<li>' . $text . '</li>');
        }

        return $this;
    }

    /**
     * Initialise et retourne le code HTML du fil d'ariane
     *
     * @return string
     */
    public function toString()
    {
        $this->init();

        return parent::toString();
    }

}
