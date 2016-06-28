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

class Password extends AbstractValidator
{

    /**
     * Message de type "ne correspondent pas"
     *
     * @const string
     */
    const PASSWORD_MISMATCH = 'passwordMismatch';

    /**
     * Message de type "absence de saisie"
     *
     * @const string
     */
    const PASSWORD_NOTSET   = 'passwordNotSet';

    /**
     * Message de type "longueur invalide"
     *
     * @const string
     */
    const PASSWORD_INVALIDLEN   = 'passwordInvalidLen';

    /**
     * Variables additionnelles
     *
     * @var array
     */
    protected $messageVariables = array(
        'min' => '_minSize',
        'max' => '_maxSize'
    );

    /**
     * Messages d'erreurs associés aux types
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::PASSWORD_MISMATCH     => "Passwords do not match",
        self::PASSWORD_NOTSET       => "The password is empty",
        self::PASSWORD_INVALIDLEN   => "The password length should be between %min% and %max%",
    );

    /**
     * Nom de l'élément
     *
     * @var string
     */
    protected $_elementName = null;

    /**
     * Nom de l'élément à comparer
     *
     * @var string
     */
    protected $_elementRepeatName = null;

    /**
     * Longueur minimale du mot de passe
     *
     * @var string
     */
    protected $_minSize = null;

    /**
     * Longueur maximale du mot de passe
     *
     * @var string
     */
    protected $_maxSize = null;

    /**
     * Configure le validateur
     *
     * @param type $name Nom de l'élément
     * @param type $repeatName Nom de l'élément à comparer
     * @param type $minSize Longueur minimale du mot de passe
     * @param type $maxSize Longueur maximale du mot de passe
     * @return self
     */
    public function __construct($name, $repeatName, $minSize = 4, $maxSize = 32)
    {
        parent::__construct();

        $this->_elementName = $name;
        $this->_elementRepeatName = $repeatName;
        $this->_minSize = $minSize;
        $this->_maxSize = $maxSize;
    }

    /**
     * Valide les mots de passe dans le contexte donné
     *
     * Le contexte est un tableau passé en deuxième paramètre
     * Retourne VRAI si valide, FAUX sinon
     *
     * @param string $value
     * @return boolean
     */
    public function isValid($value)
    {
        $context = func_get_arg(1);

        if (! isset($context[$this->_elementName]) &&
            ! isset($context[$this->_elementRepeatName])) {
            return true;
        }

        if (!isset($context[$this->_elementName]) ||
            !isset($context[$this->_elementRepeatName])) {
            $this->error(self::PASSWORD_NOTSET);

            return false;
        }

        if ($context[$this->_elementName] !== $context[$this->_elementRepeatName]) {
            $this->error(self::PASSWORD_MISMATCH);

            return false;
        }

        if (strlen($context[$this->_elementName]) < $this->_minSize ||
            strlen($context[$this->_elementName]) > $this->_maxSize ||
            strlen($context[$this->_elementRepeatName]) < $this->_minSize ||
            strlen($context[$this->_elementRepeatName]) > $this->_maxSize
        ) {
            $this->error(self::PASSWORD_INVALIDLEN);

            return false;
        }

        return true;
    }

}
