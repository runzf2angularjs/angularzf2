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

namespace Oft\View\Helper;

use Exception;
use Zend\View\Helper\AbstractHelper;

/**
 * Composant de formatage de date à partir de la locale et de la timezone
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class DateFormatter extends AbstractHelper
{
    public function __invoke($date, $dateFormatOut = 'short', $timeFormatOut = 'medium', $dateFormatIn = 'sql', $timeFormatIn = 'sql')
    {
        $dateFormatter = $this->view->app->get('DateFormatter');
        
        try {
            $datetime = $dateFormatter->generateDateTime($date, $dateFormatIn, $timeFormatIn);
            
            $datetimeFormatted = $dateFormatter->format($datetime, $dateFormatOut, $timeFormatOut);
        } catch (Exception $e) {
            $datetimeFormatted = $date;
        }
        
        return $datetimeFormatted;
    }
}
