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
 * Formulaire de saisie et d'édition d'une permission d'accès
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class AclForm extends Form
{

    /**
     * Initialisation
     */
    public function init()
    {
        $this->add(new Hidden('id_acl_resource'));
        $this->add(new Hidden('id_acl_role'));

        $groupLablel = 'Group';
        $this->add(array(
            'name' => 'group',
            'type' => 'Text',
            'attributes' => array(
                'placeholder' => $groupLablel,
            ),
            'options' => array(
                'label' => $groupLablel
            ),
        ));

        $resourceLablel = 'Resource';
        $this->add(array(
            'name' => 'resource',
            'type' => 'Text',
            'attributes' => array(
                'placeholder' => $resourceLablel,
            ),
            'options' => array(
                'label' => $resourceLablel
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Validate',
                'class' => 'btn btn-danger'
            ),
            'options' => array(
                'elm_prefix' => 0,
                'elm_size' => 12,
                'elm_align' => 'center',
            ),
        ));
    }

}
