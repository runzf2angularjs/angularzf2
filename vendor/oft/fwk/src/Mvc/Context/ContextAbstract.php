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

namespace Oft\Mvc\Context;

abstract class ContextAbstract
{
    protected $contexts = array();

    public function __construct(array $contexts = array())
    {
        foreach ($contexts as $name => $context) {
            $this->setContext($name, $context);
        }
    }

    protected function setContext($name, $context)
    {
        if (!array_key_exists($name, $this->contexts)) {
            throw new \RuntimeException("That context is not defined");
        }

        $this->contexts[$name] = $context;

        return $this;
    }

    public function __get($name)
    {
        if (!array_key_exists($name, $this->contexts)) {
            throw new \RuntimeException('That context is not defined');
        }

        return $this->contexts[$name];
    }
}
