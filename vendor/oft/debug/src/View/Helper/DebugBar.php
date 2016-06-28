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

namespace Oft\Debug\View\Helper;

use DebugBar\DebugBar as MaximeBfDebugBar;
use Zend\View\Helper\AbstractHelper;

/**
 * Rendu de la barre de debug
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class DebugBar extends AbstractHelper
{

    public function __invoke()
    {
        return $this;
    }

    public function setDebugBar(MaximeBfDebugBar $debugBar)
    {
        $this->debugBar = $debugBar;
    }

    public function __toString()
    {
        return $this->debugBar->getJavascriptRenderer()->render();
    }

}
