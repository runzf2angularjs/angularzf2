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

namespace Oft\Console\Tools\Formatter;

use Symfony\Component\Console\Formatter\OutputFormatter;

class WindowsFormatter extends OutputFormatter
{

    /**
     * Gère la transformation de l'encodage si nécessaire
     *
     * @param string $message
     * @return string
     */
    public function format($message)
    {        
        return parent::format(iconv('UTF-8', 'CP850', $message));
    }
}
