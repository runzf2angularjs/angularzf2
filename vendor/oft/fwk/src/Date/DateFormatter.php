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

namespace Oft\Date;

use DateTime;
use DateTimeZone;
use IntlDateFormatter;
use Oft\Mvc\Application;
use RuntimeException;

/**
 * Composant de formatage de date à partir de la locale et de la timezone
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class DateFormatter
{

    /**
     *  Locale
     * 
     * @var string 
     */
    protected $locale;

    /**
     *  Timezone
     * 
     * @var string 
     */
    protected $timezone;

    /**
     * 
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->timezone = $app->config['date']['timezone'];

        if ($this->timezone === null) {
            $this->timezone = date_default_timezone_get();
        }

        $translator = $app->get('Translator');

        $this->locale = $translator->getLocale();
    }

    /**
     * Retourne une date formatée dans la langue utilisateur
     * 
     * @param string|int|\DateTime $date
     * @param string|int $dateFormat
     * @param string|int $timeFormat
     * @param string $locale
     * @return type
     */
    public function format($date, $dateFormat = null, $timeFormat = 'none', $locale = null)
    {
        $pattern = null;

        if ($locale === null) {
            $locale = $this->locale;
        }

        $dateFormat = $this->getFormat($dateFormat);

        if ($dateFormat === null) {
            $pattern = 'yyyy-MM-dd';
        }

        $timeFormat = $this->getFormat($timeFormat);

        if ($timeFormat === null) {
            if ($pattern != null) {
                $pattern = $pattern . ' HH:mm:ss';
            } else {
                $pattern = 'HH:mm:ss';
            }
        }

        $formatter = new IntlDateFormatter(
            $locale, $dateFormat, $timeFormat, $this->timezone, IntlDateFormatter::GREGORIAN, $pattern
        );

        $formatedDate = $formatter->format($date);

        return $formatedDate;
    }

    /**
     * Génération d'un DateTime depuis une chaine ou timestamp
     * 
     * @param string|int $date
     * @param string|int $dateFormat
     * @param string|int $timeFormat
     * @return DateTime
     * @throws RuntimeException
     */
    public function generateDateTime($date, $dateFormat = null, $timeFormat = 'none')
    {
        if (is_numeric($date)) {
            $datetime = $this->getDateTimeFromTimestamp($date, 'U');

            return $datetime;
        }

        $pattern = null;

        $dateFormat = $this->getFormat($dateFormat);

        if ($dateFormat === null) {
            $pattern = 'yyyy-MM-dd';
        }

        $timeFormat = $this->getFormat($timeFormat);

        if ($timeFormat === null) {
            if ($pattern != null) {
                $pattern = $pattern . ' HH:mm:ss';
            } else {
                $pattern = 'HH:mm:ss';
            }
        }

        $fmt = new IntlDateFormatter(
            $this->locale, $dateFormat, $timeFormat, $this->timezone, IntlDateFormatter::GREGORIAN, $pattern
        );

        // La date doit être au format en rapport avec la locale
        // Permet de gérer les exceptions si une date n'est pas au bon format
        $fmt->setLenient(false);

        $timestamp = $fmt->parse($date);

        if ($fmt->getErrorCode() != 0) {
            $errorMessage = $fmt->getErrorMessage();

            throw new RuntimeException($errorMessage);
        }

        $datetime = new \DateTime();
        $datetime->setTimestamp($timestamp);

        return $datetime;
    }

    /**
     * Gestion des formats
     * 
     * @param string|int $format
     * @return int
     * @throws RuntimeException
     */
    protected function getFormat($format)
    {
        if ($format === null) {
            $format = IntlDateFormatter::SHORT;
        } else if (is_int($format)) {
            ; // Constant IntlDateFormatter
        } else if (is_string($format)) {
            switch ($format) {
                case 'sql' :
                    $format = null;

                    break;
                case 'none' :
                    $format = \IntlDateFormatter::NONE;

                    break;
                case 'short' :
                    $format = \IntlDateFormatter::SHORT;

                    break;
                case 'medium' :
                    $format = \IntlDateFormatter::MEDIUM;

                    break;
                case 'full' :
                    $format = \IntlDateFormatter::FULL;

                    break;
                case 'long' :
                    $format = \IntlDateFormatter::LONG;

                    break;
                case 'gregorian' :
                    $format = \IntlDateFormatter::GREGORIAN;

                    break;
                case 'traditional' :
                    $format = \IntlDateFormatter::TRADITIONAL;

                    break;
                default :
                    throw new RuntimeException('Bad parameter');
            }
        } else {
            throw new RuntimeException('Bad parameter');
        }


        return $format;
    }

    /**
     * 
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @todo
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * 
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * 
     * @param string $timezone
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * Vérification si la date est un timestamp
     * 
     * @param string|int $date
     * @param string $format
     * @return boolean
     */
    protected function getDateTimeFromTimestamp($date, $format = 'Y-m-d')
    {
        $result = null;

        $datetime = \DateTime::createFromFormat($format, $date, new DateTimeZone($this->timezone));
        $valid = DateTime::getLastErrors();

        if ($valid['warning_count'] === 0 and $valid['error_count'] === 0) {
            $result = $datetime;
        } else {
            throw new RuntimeException('Bad date format');
        }

        return $result;
    }

}
