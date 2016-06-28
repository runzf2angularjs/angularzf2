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

namespace Oft\Form\Element;

use NumberFormatter;
use Zend\Form\Element;
use Zend\InputFilter\InputProviderInterface;

/**
 * Elément de formulaire de type "Float"
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class Float extends Element implements InputProviderInterface
{
    
    /**
     * Attributs
     *
     * @var array
     */
    protected $attributes = array(
        'type' => 'text',
    );

    /**
     * Règles de filtrages et validation pour l'élément
     *
     * @return array
     */
    public function getInputSpecification()
    {
        return array(
            'name' => $this->getName(),
            'filters' => array(
                array('name' => 'Zend\Filter\StringTrim'),
                array('name' => 'Zend\Filter\StripTags'),
            ),
            'validators' => array(
                array('name' => 'Zend\I18n\Validator\IsFloat'),
            ),
        );
    }
    
}
