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

namespace Oft\Validator;

use Zend\I18n\Translator\TranslatorInterface as I18nTranslatorInterface;
use Zend\Validator\Translator\TranslatorInterface as ValidatorTranslatorInterface;

/**
 * Composant de traduction pour les validateurs
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class Translator implements ValidatorTranslatorInterface
{

    /**
     * @var I18nTranslatorInterface
     */
    protected $translator;

    /**
     * @param I18nTranslatorInterface $translator
     */
    public function __construct(I18nTranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Translate a message using the given text domain and locale
     *
     * @param string $message
     * @param string $textDomain
     * @param string $locale
     * @return string
     */
    public function translate($message, $textDomain = 'default', $locale = null)
    {
        return $this->translator->translate($message, $textDomain, $locale);
    }

}
