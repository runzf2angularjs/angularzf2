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

class Cuid extends AbstractValidator
{

    /**
     * Message de type "n'est pas un CUID valide"
     *
     * @const string
     */
    const CUID_INVALID = 'cuidInvalid';

    /**
     * Messages d'erreurs associés aux types
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::CUID_INVALID => "Invalid CUID",
    );

    /**
     * Valide le CUID donné
     *
     * Retourne VRAI si valide, FAUX sinon
     *
     * @param string $cuid
     * @return boolean
     */
    public function isValid($cuid)
    {
        if ($cuid == 'GUEST') {
            return true;
        }

        if (!preg_match('/^[a-z]{4}[0-9]{4}$/i', $cuid)) {
            $this->error(self::CUID_INVALID);

            return false;
        }

        return true;
    }

}
