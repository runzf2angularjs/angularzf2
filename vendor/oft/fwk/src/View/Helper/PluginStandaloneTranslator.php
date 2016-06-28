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

use Zend\I18n\Translator\Translator;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\View\Helper\Placeholder\Container\AbstractStandalone;

/**
 * Composant de traduction pour les aides de vue et conteneurs (placeholders)
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class PluginStandaloneTranslator extends AbstractStandalone implements TranslatorAwareInterface
{
    /**
     * Translator (optional)
     *
     * @var Translator
     */
    protected $translator;
    
    /**
     * Translator text domain (optional)
     *
     * @var string
     */
    protected $translatorTextDomain = 'default';

    /**
     * Whether translator should be used
     *
     * @var bool
     */
    protected $translatorEnabled = true;
    
    public function getTranslator()
    {
        if (! $this->isTranslatorEnabled()) {
            return null;
        }

        return $this->translator;
    }

    public function getTranslatorTextDomain()
    {
        return $this->translatorTextDomain;
    }

    public function hasTranslator()
    {
        return (bool) $this->getTranslator();
    }

    public function isTranslatorEnabled()
    {
        return $this->translatorEnabled;
    }

    public function setTranslator(TranslatorInterface $translator = null, $textDomain = null)
    {
        $this->translator = $translator;
        if (null !== $textDomain) {
            $this->setTranslatorTextDomain($textDomain);
        }

        return $this;
    }

    public function setTranslatorEnabled($enabled = true)
    {
        $this->translatorEnabled = (bool) $enabled;
        return $this;
    }

    public function setTranslatorTextDomain($textDomain = 'default')
    {
        $this->translatorTextDomain = $textDomain;
        return $this;
    }

}

