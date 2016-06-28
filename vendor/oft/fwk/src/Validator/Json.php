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

use Zend\Json\Encoder as JsonEncoder;
use Zend\Json\Exception\RuntimeException;
use Zend\Json\Json as JsonString;
use Zend\Validator\AbstractValidator;

class Json extends AbstractValidator
{

    /**
     * Message de type "n'est pas une chaîne JSON valide"
     *
     * @const string
     */
    const JSON_INVALID = 'jsonInvalid';

    /**
     * Messages d'erreurs associés aux types
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::JSON_INVALID => "The input is not a valid JSON",
    );

    /**
     * Valide la valeur donnée
     *
     * Retourne VRAI si la chaîne ne contient que des caractères JSON valides
     * FAUX sinon
     *
     * @param string $value
     * @return boolean
     */
    public function isValid($value)
    {
        try {
            $value = JsonEncoder::encodeUnicodeString($value);

            $jsonValue = JsonString::decode($value);

            $reEncodedValue =  JsonString::decode(JsonString::encode($jsonValue));

            $result = ($jsonValue == $reEncodedValue);
        } catch (RuntimeException $e) {
            $result = false;
        }

        if($result === false) {
            $this->error(self::JSON_INVALID);

            return false;
        }

        return true;
    }

}
