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

namespace Oft\Util;

class String
{

    /**
     * Transforme une chaîne au format "dash" au format "camelCase"
     *
     * Le paramètre $class permet de traiter, ou non, la chaîne
     * comme le nom d'une classe ce qui aura pour effet de transformer
     * les éventuels caractères "_" par des "\"
     * (notation des namespaces de PHP 5.3+)
     *
     * @param string $value Chaîne à transformer
     * @param bool $class Si VRAI le chaîne sera traitée comme le nom d'une classe
     * @return string
     */
    public static function dashToCamelCase($value)
    {
        return ucfirst(preg_replace_callback(
            '|([-_])([a-z])|',
            function ($what) {
                if ($what[1]=='_') {
                    return '_' . strtoupper($what[2]);
                }
                return strtoupper($what[2]);
            },
            strtolower($value)
        ));
    }

    /**
     * Transforme une chaîne au format "camelCase" au format "dash"
     *
     * @param string $value Chaîne à transformer
     * @return string
     */
    public static function camelCaseToDash($value)
    {
        return strtolower(
            str_replace('\\-', '_',
                preg_replace('/([A-Z][a-z])/', '-\1', lcfirst($value))
            )
        );
    }

    /**
     * Transforme une chaîne dans un format acceptable pour le nom d'une classe
     *
     * @param string $value Chaîne à transformer
     * @return string
     */
    public static function stringToValidClassName($value)
    {
        return self::dashToCamelCase(str_replace(
            array('_', ' ', '@', '.'),
            array('_u_', '_', '_at_', '_dot_'),
            $value
        ));
    }
    
    public static function reverseStringToValidClassName($value)
    {
        $value = str_replace('_u_', '#underscore#', strtolower($value));
        
        $value = str_replace(
            array('_at_', '_dot_', '_'),
            array('@', '.', ' '),    
            $value
        );
        
        $value = str_replace('#underscore#', '_', $value);
        
        return $value;
    }

}
