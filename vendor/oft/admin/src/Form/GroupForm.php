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
use Zend\Form\Element\Hidden;

/**
 * Formulaire de saisie et d'édition d'un groupe
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class GroupForm extends Form
{

    /**
     * Initialisation
     */
    public function init()
    {
        $this->add(new Hidden('id_acl_role'));

        $fullName = 'Full name';
        $this->add(array(
            'name' => 'fullname',
            'type' => 'Text',
            'attributes' => array(
                'placeholder' => $fullName,
            ),
            'options' => array(
                'label' => $fullName
            ),
        ));

        $name = 'Short name';
        $this->add(array(
            'name' => 'name',
            'type' => 'Text',
            'attributes' => array(
                'placeholder' => $name,
            ),
            'options' => array(
                'label' => $name
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
