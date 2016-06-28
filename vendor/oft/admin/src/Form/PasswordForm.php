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

namespace Oft\Admin\Form;

use Oft\Form\Form;
use Oft\Validator\Password;
use Zend\Form\Element\Hidden;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Validator\NotEmpty;

/**
 * Formulaire de changement de mot de passe
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class PasswordForm extends Form implements InputFilterProviderInterface
{

    /**
     * Initialisation
     */
    public function init()
    {
        $this->add(new Hidden('username'));

        $passwordLabel = 'Old password';
        $this->add(array(
            'name' => 'password',
            'type' => 'password',
            'attributes' => array(
                'placeholder' => $passwordLabel
            ),
            'options' => array(
                'label' => $passwordLabel
            ),
        ));

        $newPasswordLabel = 'New password';
        $this->add(array(
            'name' => 'new_password',
            'type' => 'password',
            'attributes' => array(
                'placeholder' => $newPasswordLabel
            ),
            'options' => array(
                'label' => $newPasswordLabel
            ),
        ));

        $newPasswordConfirmLabel = 'Confirm new password';
        $this->add(array(
            'name' => 'new_password_confirm',
            'type' => 'password',
            'attributes' => array(
                'placeholder' => $newPasswordConfirmLabel
            ),
            'options' => array(
                'label' => $newPasswordConfirmLabel
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Validate',
            ),
            'options' => array(
                'elm_size' => 2,
                'elm_nl' => false,
            ),
        ));
    }

    /**
     * Retourne les règles de validations/filtrage des champs
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return array(
            'password' => array(
                'validators' => array(
                    new Password('password', 'password'),
                    new NotEmpty(),
                )
            ),
            'new_password' => array(
                'validators' => array(
                    new Password('new_password', 'new_password_confirm'),
                    new NotEmpty(),
                )
            ),
            'new_password_confirm' => array(
                'validators' => array(
                    new Password('new_password', 'new_password_confirm'),
                    new NotEmpty(),
                )
            )
        );
    }

}
