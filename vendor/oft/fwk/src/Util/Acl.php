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

class Acl
{

    /**
     * Retourne une ressource ACL sous la forme d'un tableau à partir d'une chaîne
     *
     * @param string $resource Ressource MVC
     * @return array
     */
    public static function getMvcResourceFromString($resource)
    {
        $mvcResource = array(
            'type' => 'mvc',
            'module' => '',
            'controller' => '',
            'action' => '',
        );

        if (substr($resource, 0, 4) !== 'mvc.') {
            return null;
        }

        $mvcParts = explode('.', substr($resource, 4));

        if (!isset($mvcParts[0]) || empty($mvcParts[0])) {
            return null; // module est obligatoire
        }
        $mvcResource['module'] = $mvcParts[0];

        if (!isset($mvcParts[1]) || empty($mvcParts[1])) {
            return $mvcResource;
        }
        $mvcResource['controller'] = $mvcParts[1];

        if (!isset($mvcParts[2]) || empty($mvcParts[2])) {
            return $mvcResource;
        }
        $mvcResource['action'] = $mvcParts[2];

        return $mvcResource;
    }

}
