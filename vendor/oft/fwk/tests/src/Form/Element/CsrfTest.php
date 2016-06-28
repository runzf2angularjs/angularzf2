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

use Oft\Form\Element\Csrf;
use Oft\Form\Form;

class CsrfTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $option = array(
            'csrf_options' => array(
                'name' => 'testValidator',
            )
        );

        $element = new Csrf();
        $element->setOptions($option);

        $validator = $element->getCsrfValidator();

        $specification = $element->getInputSpecification();

        $this->assertInstanceOf('\Oft\Validator\Csrf', $validator);
        $this->assertInstanceOf('\Oft\Validator\Csrf', $specification['validators'][0]);
        $this->assertEquals(null, $element->getName());
        $this->assertTrue($specification['required']);

    }

    public function testPrepareElement()
    {
        $form = new \Zend\Form\Form();

        $validator = \Mockery::mock('\Oft\Validator\Csrf');
        $validator->shouldReceive('getHash')
            ->once()
            ->with(true)
            ->andReturn(true);

        $element = new Csrf();

        $element->setCsrfValidator($validator);

        $element->prepareElement($form);
    }

    public function testGetValue()
    {
        $validator = \Mockery::mock('\Oft\Validator\Csrf');
        $validator->shouldReceive('getHash')
            ->once()
            ->withNoArgs()
            ->andReturn('value');

        $element = new Csrf();

        $element->setCsrfValidator($validator);

        $value = $element->getValue();

        $this->assertEquals('value', $value);
    }

    public function testGetAttributes()
    {
        $validator = \Mockery::mock('\Oft\Validator\Csrf');
        $validator->shouldReceive('getHash')
            ->once()
            ->withNoArgs()
            ->andReturn('value');

        $element = new Csrf();

        $element->setCsrfValidator($validator);

        $attributes = $element->getAttributes();

        $this->assertEquals('hidden', $attributes['type']);
        $this->assertEquals('value', $attributes['value']);
    }
}
