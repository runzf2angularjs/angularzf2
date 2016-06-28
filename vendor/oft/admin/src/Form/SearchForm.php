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
 * Formulaire personnalisable de recherche
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class SearchForm extends Form
{
    /**
     * Eléments du formulaire
     *
     * @var array
     */
    protected $elementsArray;

    /**
     * Construction
     * 
     * @param string $name
     * @param array $elements
     */
    public function __construct($name, array $elements = array())
    {
        $this->elementsArray = $elements;
        parent::__construct($name);
    }

    /**
     * Initialisation
     */
    public function init()
    {
        foreach ($this->elementsArray as $element) {
            $this->add($element);
        }

        $this->add(array(
            'name' => 'submitSearch',
            'type' => 'Button',
            'attributes' => array(
                'type' => 'submit',
            ),
            'options' => array(
                'label' => 'Search',
                'elm_nl' => false,
                'elm_size' => 2,
            ),
        ));

        $this->add(array(
            'name' => 'resetSearch',
            'type' => 'Button',
            'attributes' => array(
                'type' => 'submit',
            ),
            'options' => array(
                'label' => 'Reset',
                'elm_prefix' => 0,
                'elm_size' => 2,
            ),
        ));
    }
}
