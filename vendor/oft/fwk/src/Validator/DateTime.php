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

namespace Oft\Validator;

use Exception;
use Oft\Date\DateFormatter;
use Zend\Validator\AbstractValidator;

class DateTime extends AbstractValidator
{

    /**
     * Message de type "n'est pas un CUID valide"
     *
     * @const string
     */
    const DATE_INVALID = 'dateInvalid';

    /**
     * Messages d'erreurs associés aux types
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::DATE_INVALID => "Date has an invalid format",
    );
    
    /** @var DateFormatter */
    protected $dateFormatter;
    protected $dateFormat;
    protected $dateSqlFormat;
    protected $timeFormat;
    protected $timeSqlFormat;

    public function __construct($dateFormatter, $dateFormat, $dateSqlFormat, $timeFormat, $timeSqlFormat, $options = null)
    {
        parent::__construct($options);
        
        $this->dateFormatter = $dateFormatter;
        $this->dateFormat = $dateFormat;
        $this->dateSqlFormat = $dateSqlFormat;
        $this->timeFormat = $timeFormat;
        $this->timeSqlFormat = $timeSqlFormat;
    }
    
    public function isValid($value)
    {
        try {
            // Localized format
            $this->dateFormatter->generateDateTime($value, $this->dateFormat, $this->timeFormat);
        } catch (Exception $e) {
            try {
                // ... or SQL format
                $this->dateFormatter->generateDateTime($value, $this->dateSqlFormat, $this->timeSqlFormat);
            } catch (Exception $e) {
                $this->error(self::DATE_INVALID);

                return false;
            }
        }

        return true;
    }

}
