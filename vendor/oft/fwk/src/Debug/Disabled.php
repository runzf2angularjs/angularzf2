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

namespace Oft\Debug;

class Disabled implements \Oft\Debug\DebugInterface
{

    public function addException(\Exception $e)
    {
        return;
    }

    public function addMessage($message, $type)
    {
        return;
    }

    public function isDebug()
    {
        return false;
    }

    public function dump($var, $title = null, $return = false)
    {

    }

}
