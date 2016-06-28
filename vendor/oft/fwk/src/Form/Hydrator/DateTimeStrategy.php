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

namespace Oft\Form\Hydrator;

use Exception;
use Oft\Date\DateFormatter;
use Oft\Form\Element\DateTime;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

/**
 * Stratégie d'alimentation et d'extraction d'une valeur d'un élément de formulaire de type "DateTime"
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class DateTimeStrategy implements StrategyInterface
{
    /**
     * @var DateFormatter
     */
    protected $dateFormater;

    protected $dateFormat;
    protected $dateSqlFormat;
    protected $timeFormat;
    protected $timeSqlFormat;

    public function __construct(DateTime $dateTime)
    {
        $this->dateFormatter = $dateTime->getDateFormatter();
        
        $this->dateFormat = $dateTime->getAttribute('dateFormat');
        $this->dateSqlFormat = $dateTime->getAttribute('dateSqlFormat');
        $this->timeFormat = $dateTime->getAttribute('timeFormat');
        $this->timeSqlFormat = $dateTime->getAttribute('timeSqlFormat');
    }

    public function extract($date)
    {
        if (empty($date)) {
            return null;
        }

        try {
            $dateTime = $this->dateFormatter->generateDateTime($date, $this->dateSqlFormat, $this->timeSqlFormat);
            return $this->dateFormatter->format($dateTime, $this->dateFormat, $this->timeFormat);
        } catch (Exception $e) {
            // Return as-is
            return $date;
        }
    }

    public function hydrate($date)
    {
        if (empty($date)) {
            return null;
        }
        
        try {
            $dateTime = $this->dateFormatter->generateDateTime($date, $this->dateFormat, $this->timeFormat);
            return $this->dateFormatter->format($dateTime, $this->dateSqlFormat, $this->timeSqlFormat);
        } catch (Exception $e) {
            // Return as-is
            return $date;
        }
    }

}
