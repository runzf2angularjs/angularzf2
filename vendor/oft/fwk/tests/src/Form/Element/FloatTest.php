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

namespace Oft\Test\Form\Element;

use NumberFormatter;
use Oft\Form\Element\Float;
use PHPUnit_Framework_TestCase;

class FloatTest extends PHPUnit_Framework_TestCase
{
    
    public function testGetInputSpecification()
    {
        $name = 'float_test';
        $element = new Float($name);
        
        $input = $element->getInputSpecification();

        $filters = array(
            array('name' => 'Zend\Filter\StringTrim'),
            array('name' => 'Zend\Filter\StripTags'),
        );

        $validators = array(
            array('name' => 'Zend\I18n\Validator\IsFloat'),
        );
        
        $this->assertEquals($name, $input['name']);
        $this->assertEquals($filters, $input['filters']);
        $this->assertEquals($validators, $input['validators']);
    }
    
}
