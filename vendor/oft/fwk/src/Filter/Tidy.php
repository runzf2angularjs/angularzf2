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

use tidy as FilterTidy;
use Zend\Filter\AbstractFilter;

class Tidy extends AbstractFilter
{

    /**
     * Instance de tidy
     *
     * @var FilterTidy
     */
    protected $tidy;

    /**
     * Encodage par défaut
     *
     * @var string
     */
    protected $encoding = 'UTF8';

    /**
     * Configuration de tidy
     *
     * @var array
     */
    protected $config = array(
        'indent' => true,
        'indent-attributes' => true,
        'output-html' => true, // G1 : output-xhtml
        'wrap' => false,
        'show-body-only' => true,
        'drop-proprietary-attributes' => false, // G1 : true
    );

    /**
     * Retourne le contenu donné filtré
     *
     * @param string $content Contenu à filtrer
     * @return string
     */
    public function filter($content)
    {
        $tidy = new FilterTidy();
        $tidy->parseString($content, $this->config, $this->encoding);
        $tidy->cleanRepair();

        return (string)$tidy;
    }

    /**
     * Définit la configuration de tidy
     *
     * @param array $config Configuration
     * @return void
     */
    public function setConfig(array $config = array())
    {
        $this->config = $config;
    }

    /**
     * Définit l'encodage utilisé
     *
     * @param string $encoding Encodage
     * @throws \RuntimeException
     * @return void
     */
    public function setEncoding($encoding)
    {
        if (!is_string($encoding)) {
            throw new \RuntimeException('Le paramètre "encodage" doit être une chaîne');
        }
        $this->encoding = $encoding;
    }

}
