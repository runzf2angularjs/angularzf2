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

/**
 * Formulaire de confirmation de suppression
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class DeleteForm extends Form
{

    /**
     * Initialisation
     */
    public function init()
    {
        $this->add(array(
            'type' => 'Submit',
            'name' => 'submit',
            'attributes' => array(
                'value' => 'Delete',
                'class' => 'btn btn-primary btn-danger',
            ),
            'options' => array(
                'elm_prefix' => 0,
                'elm_size' => 12,
                'elm_align' => 'center',
            ),
        ));
    }

}
