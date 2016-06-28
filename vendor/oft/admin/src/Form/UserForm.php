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

use Zend\Form\Element\Hidden;
use Oft\Form\Form;

/**
 * Formulaire de saisie et d'édition d'un utilisateur
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class UserForm extends Form
{

    /**
     * Configure l'auto-complétion sur le champ "username" et active le bouton de recherche
     *
     * @param string $url
     */
    public function setAutocomplete($url)
    {
        $this->get('username')->setAttribute('data-url', $url);
        $this->get('search')->setAttribute('disabled', null);
    }

    /**
     * Initialisation
     */
    public function init()
    {
        $this->add(new Hidden('id_user'));

        $usernameLabel = 'Username';
        $this->add(array(
            'name' => 'username',
            'type' => 'Text',
            'attributes' => array(
                'placeholder' => $usernameLabel,
            ),
            'options' => array(
                'label' => $usernameLabel,

                'elm_nl' => false,
                'elm_size' => 4,
            ),
        ));

        $searchLabel = 'Prefill';
        $this->add(array(
            'name' => 'search',
            'type' => 'Button',
            'attributes' => array(
                'placeholder' => $searchLabel,

                // Search GIR
                'id' => 'users-search-gir',
                'disabled' => 'disabled',
            ),
            'options' => array(
                'label' => $searchLabel,

                'label_size' => 0,
                'elm_size' => 2,
                'elm_align' => 'right',
            ),
        ));

        $passwordLabel = 'Password';
        $this->add(array(
            'name' => 'password',
            'type' => 'password',
            'attributes' => array(
                'placeholder' => $passwordLabel,
                'autocomplete' => 'off'
            ),
            'options' => array(
                'label' => $passwordLabel
            ),
        ));

        $passwordConfirmLabel = 'Confirm Password';
        $this->add(array(
            'name' => 'password_confirm',
            'type' => 'password',
            'attributes' => array(
                'placeholder' => $passwordConfirmLabel
            ),
            'options' => array(
                'label' => $passwordConfirmLabel
            ),
        ));


        $activeLabel = 'Active';
        $this->add(array(
            'name' => 'active',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'value' => '1',
            ),
            'options' => array(
                'label' => $activeLabel,
                'value_options' => array(
                    '1' => 'Yes',
                    '0' => 'No',
                ),
            ),
        ));

        $preferred_languageLabel = 'Language';
        $this->add(array(
            'name' => 'preferred_language',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'value' => 'FR',
            ),
            'options' => array(
                'label' => $preferred_languageLabel,
                'value_options' => array(
                    'FR' => 'fr',
                    'EN' => 'en',
                ),
            ),
        ));

        $civilityLabel = 'Civility';
        $this->add(array(
            'name' => 'civility',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => $civilityLabel,
                'value_options' => array(
                    '0' => '-',
                    '1' => 'Mr',
                    '2' => 'Mrs',
                    '3' => 'Ms',
                ),
            ),
        ));

        $givennameLabel = 'First name';
        $this->add(array(
            'name' => 'givenname',
            'type' => 'Text',
            'attributes' => array(
                'placeholder' => $givennameLabel,
            ),
            'options' => array(
                'label' => $givennameLabel
            ),
        ));

        $surnameLabel = 'Surname';
        $this->add(array(
            'name' => 'surname',
            'type' => 'Text',
            'attributes' => array(
                'placeholder' => $surnameLabel,
            ),
            'options' => array(
                'label' => $surnameLabel
            ),
        ));

        $mailLabel = 'Mail';
        $this->add(array(
            'name' => 'mail',
            'type' => 'Text',
            'attributes' => array(
                'placeholder' => $mailLabel
            ),
            'options' => array(
                'label' => $mailLabel
            ),
        ));

        $entityLabel = 'Entity';
        $this->add(array(
            'name' => 'entity',
            'type' => 'Text',
            'attributes' => array(
                'placeholder' => $entityLabel
            ),
            'options' => array(
                'label' => $entityLabel
            ),
        ));

        $managerUsernameLabel = 'Manager Username';
        $this->add(array(
            'name' => 'manager_username',
            'type' => 'Text',
            'attributes' => array(
                'placeholder' => $managerUsernameLabel
            ),
            'options' => array(
                'label' => $managerUsernameLabel
            ),
        ));

        $groupLabel = 'Groups';
        $this->add(array(
            'name' => 'groups',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'multiple' => 'multiple',
            ),
            'options' => array(
                'label' => $groupLabel,
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

        $this->add(array(
            'name' => 'reset',
            'type' => 'Button',
            'attributes' => array(
                'type' => 'reset',
            ),
            'options' => array(
                'label' => 'Reset',
                'elm_prefix' => 0,
                'label_size' => 0,
                'label_nl' => false,
                'elm_size' => 2,
                'elm_nl' => true,
            ),
        ));
    }

}
