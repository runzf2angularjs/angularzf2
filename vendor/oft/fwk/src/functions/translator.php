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

/**
 * Traduction de chaine
 * 
 * @staticvar \Zend\I18n\Translator\Translator $translator
 * @param string $string
 * @param string $textDomain
 * @param string $locale
 * @return string
 */
function __($string)
{
    static $translator = null;
    
    if (!$translator) {
        $app = \Oft\Util\Functions::getApp();
        $translator = $app->get('Translator');
    }
    
    $translated = $translator->translate($string);
    
    if (func_num_args() > 1) {
        $args = func_get_args();
        unset($args[0]);
           
        $translated = vsprintf($translated, $args);
    }

    return $translated;
}
