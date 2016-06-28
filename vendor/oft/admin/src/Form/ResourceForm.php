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
 * Formulaire de saisie et d'édition d'une ressource
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class ResourceForm extends Form
{

    /**
     * Initialisation
     */
    public function init()
    {
        $this->add(new Hidden('id_acl_resource'));

        $nameLabel = 'Name';
        $this->add(array(
            'name' => 'name',
            'type' => 'Text',
            'attributes' => array(
                'placeholder' => $nameLabel,
            ),
            'options' => array(
                'label' => $nameLabel
            ),
        ));

        $typeLabel = 'Type';
        $this->add(array(
            'name' => 'type',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => $typeLabel,
                'value_options' => array(
                    'mvc' => 'Page Access (MVC)',
                ),
            ),
        ));

        $moduleLabel = 'Module';
        $this->add(array(
            'name' => 'module',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => $moduleLabel,
            ),
        ));

        $controllerLabel = 'Controller';
        $this->add(array(
            'name' => 'controller',
            'type' => 'Text',
            'attributes' => array(
                'placeholder' => $controllerLabel
            ),
            'options' => array(
                'label' => $controllerLabel
            ),
        ));

        $actionLabel = 'Action';
        $this->add(array(
            'name' => 'action',
            'type' => 'Text',
            'attributes' => array(
                'placeholder' => $actionLabel
            ),
            'options' => array(
                'label' => $actionLabel
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
