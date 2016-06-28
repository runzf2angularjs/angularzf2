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

class Title extends PluginStandaloneTranslator
{

    /**
     * @var String
     */
    protected $appName;
    
    /**
     *
     * @var boolean 
     */
    protected $init = false;

    /**
     * Concatène la chaîne donnée au titre de l'application.
     *
     * @param string $text Titre à concaténer.
     * @return self
     */
    public function __invoke($text = null)
    {
        $placeholder = $this->getContainer();
        
        if (!$this->init) {
            $placeholder
                ->setPrefix('<span>')
                ->setSeparator(' - ')
                ->setPostfix('</span>')
                ->set(e($this->appName));

            $this->view->headTitle($this->appName, 'SET');

            $this->init = true;
        }
                
        if ($text !== null) {
            $text = $this->getTranslator()->translate($text);
            
            $this->view->headTitle($text . ' - ' . $this->appName, 'SET');
            $placeholder->prepend($text);
        }

        return $this;
    }

    /**
     * Initialisation du titre de l'application et du helper HeadTitle().
     *
     * @return void
     */
    public function setAppName($appName)
    {
        $this->appName = $this->getTranslator()->translate($appName);

        return $this;
    }
    
    /**
     * 
     * @return string
     */
    public function getAppName()
    {
        return $this->appName;
    }

}
