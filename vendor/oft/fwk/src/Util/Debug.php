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

class Debug
{

    /**
     * Affiche le résultat de "print_r" sur la variable donnée
     *
     * @param type $var Variable à afficher
     * @param type $title Titre facultatif de l'affichage
     * @param type $return Si VRAI, retourne le résultat au lieu de l'afficher
     * @return string
     */
    public static function dump($var, $title = null, $return = false)
    {
        $content = '';
        if (is_string($title)) {
            $content .= '<strong>' . $title . '</strong>';
        }
        $content .= '<pre>' . print_r($var, true) . '</pre>';

        if ($return) {
            return $content;
        }

        echo $content;
    }

}
