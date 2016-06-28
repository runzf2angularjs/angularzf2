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

namespace Oft\Test\Form;

use Oft\Form\Form;
use PHPUnit_Framework_TestCase;

class FormTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $form = new Form(null, array('csrf_name' => 'testCsrf'));
        
        $this->assertInstanceOf('Zend\Form\Form', $form);
        $this->assertTrue($form->has('testCsrf'));
    }
    
    public function testGetCsrf()
    {
        $form = new Form();
        
        $csrf = $form->get('csrf');
        
        $this->assertInstanceOf('Oft\Form\Element\Csrf', $csrf);
        $this->assertEquals('csrf', $csrf->getName());
    }
    
    public function testIsValid()
    {        
        $app = \Oft\Test\Mock\ApplicationMock::factory();
        $app->http->session->shouldReceive('getContainer')
            ->once()
            ->andReturn(new \stdClass());
        
        \Oft\Util\Functions::setApp($app);
        
        $form = new Form();
        
        $form->add(array(
            'name' => 'sprint_id',
            'type' => 'Hidden',
            'input_filter' => array(
                'validators' => array(
                    new \Zend\Validator\Digits()
                )
            )
        ));
        
        $form->setData(array(
            'csrf' => 'test',
            'sprint_id' => 'test',
        ));
        
        $form->isValid();
        
        $messages = $form->getMessages();
        
        $this->assertEquals(1, count($messages));
        
        \Oft\Util\Functions::setApp();
    }
    
    public function testFlagNameAdd()
    {
        $el = array(
            'type' => 'Text',
        );
        
        $form = new Form();
        $form->add($el, array('name' => 'test'));
        
        $element = $form->get('test');
        
        $this->assertInstanceOf('\Zend\Form\Element\Text', $element);
    }

    public function testHydratorDateTime()
    {
        $app = new \Oft\Mvc\Application();

        $dateFormatter = \Mockery::mock('\Oft\Date\DateFormatter');

        $app->setService('DateFormatter', $dateFormatter);

        \Oft\Util\Functions::setApp($app);

        $dateTime = new \Oft\Form\Element\DateTime('datetime');

        $form = new Form();
        $form->add($dateTime);

        $strategie = $form->getHydrator()->getStrategy('datetime');

        $this->assertInstanceOf('Oft\Form\Hydrator\DateTimeStrategy', $strategie);
    }

    public function testHydratorFloat()
    {
        $float = new \Oft\Form\Element\Float('float');

        $form = new Form();
        $form->add($float);

        $strategie = $form->getHydrator()->getStrategy('float');

        $this->assertInstanceOf('Oft\Form\Hydrator\FloatStrategy', $strategie);
    }
    
    public function testIsValidCsrf()
    {
        $csrf = \Mockery::mock('\Oft\Form\Element\Csrf');
        $csrf->shouldReceive('getName')
            ->andReturn('csrf');
        $csrf->shouldReceive('getCsrfValidator')
            ->once()
            ->andReturn(new \Zend\Validator\NotEmpty());
        $csrf->shouldReceive('setMessages')
            ->andReturn(true);
        $csrf->shouldReceive('getMessages')
            ->andReturn(array('messageCsrf'));
        
        $form = new Form();
        $form->remove('csrf');
        $form->getInputFilter()->remove('csrf');
        
        $form->add($csrf);
        $isValid = $form->isValid();
        
        $this->assertFalse($isValid);
        
        $messages = $form->getMessages();
        
        $this->assertEquals(array('csrf' => array(0 => 'messageCsrf')), $messages);
        
        $isValidSecond = $form->isValid();
        $this->assertFalse($isValidSecond);
    }
    
    public function testIsValidCsrfFirst()
    {
        $csrf = \Mockery::mock('\Oft\Form\Element\Csrf');
        $csrf->shouldReceive('getName')
            ->andReturn('csrf');
        $csrf->shouldReceive('getCsrfValidator')
            ->once()
            ->andReturn(new \Zend\Validator\NotEmpty());
        $csrf->shouldReceive('setValue')
            ->andReturn(true);
        $csrf->shouldReceive('getMessages')
            ->andReturn(array());
        
        $form = new Form();
        $form->remove('csrf');
        $form->getInputFilter()->remove('csrf');
        
        $form->add($csrf);
        $form->setData(array('csrf' => 'test'));
        $isValid = $form->isValid();
        
        $this->assertTrue($isValid);
        
        $messages = $form->getMessages();
        
        $this->assertEquals(array(), $messages);
        
        $isValidSecond = $form->isValid();
        $this->assertTrue($isValidSecond);
    }
    
    public function testTimeoutCsrf()
    {
        $option = array(
            'csrf_options' => array (
                'timeout' => 10,
            ),
        );
        
        $form = new Form('form', $option);
        
        $csrf = $form->get('form_csrf');
        
        $csrfOptions = $csrf->getCsrfValidatorOptions();
        
        $this->assertEquals(10, $csrfOptions['timeout']);
    }
    
}
