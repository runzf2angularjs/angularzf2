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

namespace Oft\Auth\Form;

use Oft\Form\Form;
use Zend\Filter\StringTrim;
use Zend\Filter\StripTags;
use Zend\Validator\StringLength;

/**
 * Formulaire de connexion
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class LoginPasswordForm extends Form //implements InputFilterProviderInterface
{

    public function __construct($options = array())
    {
        parent::__construct('login', $options);
    }

    /**
     * Initialisation du formulaire
     *
     * @return void
     */
    public function init()
    {
        $this->add(array(
            'name' => 'username',
            'type' => 'Text',
            'attributes' => array(
                'class' => 'form-control',
                'placeholder' => 'Username'
            ),
            'options' => array(
                'label' => 'Username'
            ),
            'input_filter' => array(
                'filters' => array(
                    new StripTags(),
                    new StringTrim()
                ),
            )
        ));

        $this->add(array(
            'name' => 'password',
            'type' => 'Password',
            'attributes' => array(
                'class' => 'form-control',
                'placeholder' => 'Password'
            ),
            'options' => array(
                'label' => 'Password'
            ),
            'input_filter' => array(
                'filters' => array(
                    new StripTags(),
                    new StringTrim()
                ),
                'validators' => array(
                    new StringLength(1, 30)
                )
            )
        ));

        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Log in',
                'class' => 'btn btn-primary'
            )
        ));
    }

}
