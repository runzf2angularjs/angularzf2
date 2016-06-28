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
 * Echappement de données
 *
 * @todo Echappement JSON
 * @param string $string Chaîne à échapper
 * @param array $type Type d'échappement
 * @throws \DomainException
 * @return string
 */
function e($string, $type = 'html')
{
    static $escaper = null;

    if (!$escaper) {
        $encoding = \Oft\Util\Functions::getApp()->config['escaper']['encoding'];
        $escaper = new \Zend\Escaper\Escaper($encoding);
    }

    switch ($type) {
        case 'html':
        case 'xml':
            return $escaper->escapeHtml($string);
        case 'htmlAttr':
            return $escaper->escapeHtmlAttr($string);
        case 'js':
            return $escaper->escapeJs($string);
        case 'css':
            return $escaper->escapeCss($string);
        case 'url':
            return $escaper->escapeUrl($string);
        default:
            throw new \DomainException(
                'La méthode d\'échappement associée au type "' . $type . '" n\'existe pas'
            );
    }
}

/**
 * Echappement de code HTML
 *
 * @param string $string Chaîne à échapper
 * @return string
 */
function eHtml($string)
{
    return e($string, 'html');
}

/**
 * Echappement de code XML
 *
 * @param string $string Chaîne à échapper
 * @return string
 */
function eXml($string)
{
    return e($string, 'xml');
}

/**
 * Echappement d'attributs de code HTML
 *
 * @param string $string Chaîne à échapper
 * @return string
 */
function eHtmlAttr($string)
{
    return e($string, 'htmlAttr');
}

/**
 * Echappement de code JavaScript
 *
 * @param string $string Chaîne à échapper
 * @return string
 */
function eJs($string)
{
    return e($string, 'js');
}

/**
 * Echappement de code CSS
 *
 * @param string $string Chaîne à échapper
 * @return string
 */
function eCss($string)
{
    return e($string, 'css');
}

/**
 * Echappement d'URL
 *
 * @param string $string Chaîne à échapper
 * @return string
 */
function eUrl($string)
{
    return e($string, 'url');
}
