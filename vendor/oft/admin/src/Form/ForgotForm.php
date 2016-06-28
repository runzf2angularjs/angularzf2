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
use Zend\Captcha\Dumb;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Validator\NotEmpty;

/**
 * Formulaire de demande de réinitialisation de mot de passe
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class ForgotForm extends Form implements InputFilterProviderInterface
{

    /**
     * Initialisation
     */
    public function init()
    {
        $usernameLabel = 'Username';
        $this->add(array(
            'name' => 'username',
            'type' => 'text',
            'attributes' => array(
                'placeholder' => $usernameLabel
            ),
            'options' => array(
                'label' => $usernameLabel
            ),
        ));

        $this->add(array(
            'name' => 'captcha',
            'type' => 'captcha',
            'options' => array(
                'label' => 'Security',
                'captcha' => new Dumb(array(
                    'wordlen' => 4,
                    'label' => __('Please type this word backwards') . ' : ',
                    'messages' => array(
                        'badCaptcha' => __('Bad Captcha'),
                    ),
                )),
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Validate',
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
            'username' => array(
                'validators' => array(
                    new NotEmpty(),
                ),
            ),
        );
    }

}
