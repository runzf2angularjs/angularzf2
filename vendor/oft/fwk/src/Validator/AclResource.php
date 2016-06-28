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

use Zend\Validator\AbstractValidator;

class AclResource extends AbstractValidator
{

    /**
     * Message de type "n'est pas une chaîne"
     *
     * @const string
     */
    const INVALID      = 'alphaInvalid';

    /**
     * Message de type "n'est pas une ressource ACL"
     *
     * @const string
     */
    const NOT_RESOURCE = 'notResource';

    /**
     * Message de type "est une chaîne vide"
     *
     * @const string
     */
    const STRING_EMPTY = 'alphaStringEmpty';

    /**
     * Messages d'erreurs associés aux types
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID      => "Invalid type given. String expected",
        self::NOT_RESOURCE => "'%value%' is not a valid ACL",
        self::STRING_EMPTY => "The input is an empty string"
    );

    /**
     * Valide la valeur donnée
     *
     * Retourne VRAI si valide, FAUX sinon
     *
     * @param string $value
     * @return boolean
     */
    public function isValid($value)
    {
        if ($value === null) {
            return true;
        }

        if (!is_string($value)) {
            $this->error(self::INVALID);

            return false;
        }

        $this->setValue($value);

        if ('' === $value) {
            $this->error(self::STRING_EMPTY);

            return false;
        }

        if (!preg_match('/^[a-z][a-z0-9\.-]*[a-z0-9]$/iu', $value)) {
            $this->error(self::NOT_RESOURCE);

            return false;
        }

        return true;
    }

}
