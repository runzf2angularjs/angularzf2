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

namespace Oft\Filter;

use HTMLPurifier as FilterHTMLPurifier;
use HTMLPurifier_Config;
use Zend\Filter\AbstractFilter;

class Htmlpurifier extends AbstractFilter
{

    /**
     * Configuration de HTMLPurifier
     *
     * @var array
     */
    public static $config = null;

    /**
     * Retourne le contenu donné filtré
     *
     * @param string $content Contenu à filtrer
     * @return string
     */
    public function filter($content)
    {
        $htmlPurifier = new FilterHTMLPurifier($this->getConfig());

        return $htmlPurifier->purify($content);
    }

    /**
     * Retourne la configuration de HTMLPurifier
     *
     * S'appuie sur un composant interne à HTMLPurifier
     *
     * @return HTMLPurifier_Config
     */
    public function getConfig()
    {
        if (self::$config === null) {
            self::$config = HTMLPurifier_Config::createDefault();
            self::$config->set('Core.Encoding', 'UTF-8');
            self::$config->set('Cache.SerializerPath', CACHE_DIR);
        }

        return self::$config;
    }

}
