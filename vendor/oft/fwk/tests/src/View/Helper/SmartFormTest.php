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

namespace Oft\Test\View\Helper;

include_once __DIR__ . '/_files/functions.php';

class SmartFormTest extends \PHPUnit_Framework_TestCase
{
    protected $testClass;
    protected $reflection;

    public function setUp()
    {
        $this->testClass = new \Oft\View\Helper\SmartForm();
        $this->reflection = new \ReflectionClass($this->testClass);
        parent::setup();
    }

    public function tearDown()
    {
        $this->testClass = null;
        $this->reflection = null;
    }

    public function getMethod($method)
    {
        $method = $this->reflection->getMethod($method);
        $method->setAccessible(true);
        return $method;
    }

    public function getProperty($property)
    {
        $property = $this->reflection->getProperty($property);
        $property->setAccessible(true);
        return $property->getValue($this->testClass);
    }

    public function setProperty($property, $value)
    {
        $property = $this->reflection->getProperty($property);
        $property->setAccessible(true);
        return $property->setValue($this->testClass, $value);
    }

    public function testRenderFieldset()
    {
        $expected = "<fieldset name=\"test\" id=\"test\">\n"
            . "<legend>monlabel</legend>\n"
            . "</fieldset>\n";

        $helperConfig = new \Zend\Form\View\HelperConfig();

        $helperManagerPlugin = new \Zend\View\HelperPluginManager();
        $helperConfig->configureServiceManager($helperManagerPlugin);

        $view = new \Oft\View\View();
        $view->setHelperPluginManager($helperManagerPlugin);

        $fieldset = new \Zend\Form\Fieldset('test');
        $fieldset->setLabel('monlabel');

        $helper = new \Oft\View\Helper\SmartForm();
        $helper->setView($view);

        $html = $helper->renderFieldset($fieldset);

        $this->assertEquals($expected, $html);
    }

    public function testRenderFieldsetWithFieldset()
    {
        $expected = "<fieldset name=\"test\" id=\"test\">\n"
            . "<legend>monlabel</legend>\n"
            . "<div class=\"container-fluid\">\n"
            . "<fieldset name=\"test1\" id=\"test1\">\n"
            . "<legend>monlabel1</legend>\n"
            . "</fieldset>\n"
            . "</div>\n"
            . "</fieldset>\n";

        $helperConfig = new \Zend\Form\View\HelperConfig();

        $helperManagerPlugin = new \Zend\View\HelperPluginManager();
        $helperConfig->configureServiceManager($helperManagerPlugin);

        $view = new \Oft\View\View();
        $view->setHelperPluginManager($helperManagerPlugin);

        $fieldset1 = new \Zend\Form\Fieldset('test1');
        $fieldset1->setLabel('monlabel1');

        $fieldset = new \Zend\Form\Fieldset('test');
        $fieldset->setLabel('monlabel');
        $fieldset->add($fieldset1);

        $helper = new \Oft\View\Helper\SmartForm();
        $helper->setView($view);

        $html = $helper->renderFieldset($fieldset);

        $this->assertEquals($expected, $html);
    }

    public function testReset()
    {
        $default = $this->getProperty('defaultOptions');

        $this->assertEquals(null, $this->getProperty('options'));

        $method = $this->getMethod("reset");

        $method->invoke($this->testClass);

        $this->assertEquals($default, $this->getProperty('options'));
    }

    public function testMergeOptions()
    {
        $expected = array(
            'test1' => 'test3',
            'attr_role_form' => 'form',
            'attr_class_form' => 'form-horizontal'
        );

        $options = array('test1' => 'test3');

        $this->setProperty('options', array(
            'test1' => 'test2',
        ));

        $method = $this->getMethod("mergeOptions");

        $method->invoke($this->testClass, $options);

        $this->assertEquals($expected, $this->getProperty('options'));
    }

    public function testGetFormOpenTag()
    {
        $expected = '<form action="" method="POST" name="test" role="" id="test" class="">';

        $form = new \Oft\Form\Form('test');

        $helperForm = new \Zend\Form\View\Helper\Form();

        $helper = new \Oft\View\Helper\SmartForm();

        $html = $helper->getFormOpenTag($form, $helperForm);

        $this->assertEquals($expected, $html);
    }

    public function testGetFormCloseTag()
    {
        $expected = '</form>';

        $helper = new \Oft\View\Helper\SmartForm();

        $html = $helper->getFormCloseTag();

        $this->assertEquals($expected, $html);
    }

    public function testIsElementRequired()
    {
        $inputFilter2 = new \Zend\InputFilter\InputFilter();
        $inputFilter2->add(array(
            'name' => 'input',
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'not_empty',
                ),
            ),
        ));

        $inputFilter1 = new \Zend\InputFilter\InputFilter();
        $inputFilter1->add($inputFilter2, 'test2');

        $inputFilter = new \Zend\InputFilter\InputFilter();
        $inputFilter->add($inputFilter1, 'test1');

        $this->setProperty('formInputFilter', $inputFilter);

        $fieldsetPath[] = 'test1';
        $fieldsetPath[] = 'test1[test2]';

        $this->setProperty('fieldsetPath', $fieldsetPath);

        $method = $this->getMethod("isElementRequired");

        $elm = new \Zend\Form\Element\Text('test1[test2][input]');

        $required = $method->invoke($this->testClass, $elm);

        $this->assertTrue($required);
    }

    public function testIsElementNotRequired()
    {
        $inputFilter2 = new \Zend\InputFilter\InputFilter();
        $inputFilter2->add(array(
            'name' => 'input',
            'required' => false,
        ));

        $inputFilter1 = new \Zend\InputFilter\InputFilter();
        $inputFilter1->add($inputFilter2, 'test2');

        $inputFilter = new \Zend\InputFilter\InputFilter();
        $inputFilter->add($inputFilter1, 'test1');

        $this->setProperty('formInputFilter', $inputFilter);

        $fieldsetPath[] = 'test1';
        $fieldsetPath[] = 'test1[test2]';

        $this->setProperty('fieldsetPath', $fieldsetPath);

        $method = $this->getMethod("isElementRequired");

        $elm = new \Zend\Form\Element\Text('test1[test2][input]');

        $required = $method->invoke($this->testClass, $elm);

        $this->assertFalse($required);
    }

    public function testFieldsetOpenTag()
    {
        $helperForm = new \Zend\Form\View\Helper\Form();

        $fieldset = new \Zend\Form\Fieldset('test');

        $helper = new \Oft\View\Helper\SmartForm();

        $html = $helper->getFieldsetOpenTag($fieldset, $helperForm);

        $this->assertEquals('<fieldset name="test" id="test">', $html);
    }

    /**
     * Bug #631
     * Vérifier que les options de SmartForm sont passées à smartElement
     */
    public function testRenderElements()
    {
        $expected = "<div class=\"container-fluid\">\n"
            . "<fieldset name=\"fieldsetName\" id=\"fieldsetName\">\n"
            . "<div class=\"container-fluid\">\n"
            . "<div class=\"form-group\">\n"
            . "text\n"
            . "</div>\n"
            . "</div>\n"
            . "</fieldset>\n"
            . "</div>\n";

        $text = new \Zend\Form\Element\Text('text');

        $fieldset = new \Zend\Form\Fieldset('fieldsetName');
        $fieldset->add($text);

        $form = new \Zend\Form\Form('formName');
        $form->add($fieldset);

        $form->getInputFilter()->get('fieldsetName')->get('text')->setRequired(true);

        $elements = $form->getIterator()->toArray();

        $view = \Mockery::mock('\Oft\View\View');
        $view->shouldReceive('smartElement')
            ->once()
            ->with($form->get('fieldsetName')->get('text'), array(
                'attr_role_form' => 'form',
                'attr_class_form' => 'form-horizontal',
                'show_descriptions' => false,
                'required'=>'true'
            ))
            ->andReturn('text');

        $formHelper = new \Zend\Form\View\Helper\Form();

        $view->shouldReceive('form')
            ->once()
            ->andReturn($formHelper);

        $helper = new \Oft\View\Helper\SmartForm();
        $helper->mergeOptions(array('show_descriptions' => false));
        $helper->setView($view);

        $helper->setFormInputFilter($form->getInputFilter());

        $html = $helper->renderElements($elements);

        $this->assertEquals($expected, $html);
    }

    public function testInvoke()
    {
        $expected = "<form action=\"\" method=\"POST\" role=\"form\" class=\"form-horizontal\">\n"
            . "<div class=\"container-fluid\">\n"
            . "<div class=\"form-group\">\n"
            . "input\n"
            . "input\n"
            . "</div>\n"
            . "</div>\n"
            . "</form>\n";

        $text = new \Zend\Form\Element\Text('text');
        $text->setOption('elm_nl', false);
        $text2 = new \Zend\Form\Element\Text('text2');
        $text2->setOption('elm_nl', false);

        $form = new \Oft\Form\Form();
        $form->remove('csrf');
        $form->getInputFilter()->remove('csrf');
        $form->add($text);
        $form->add($text2);

        $formHelper = new \Zend\Form\View\Helper\Form();

        $view = \Mockery::mock('\Oft\View\View');
        $view->shouldReceive('form')
            ->once()
            ->andReturn($formHelper);
        $view->shouldReceive('smartElement')
            ->twice()
            ->andReturn('input');

        $helper = new \Oft\View\Helper\SmartForm();
        $helper->setView($view);

        $html = $helper->__invoke($form);

        $this->assertEquals($html, $expected);
    }

    public function testGetFieldsetSimpleOpenTag()
    {
        $plugin = new \Oft\View\Helper\SmartForm();

        $html = $plugin->getFieldsetSimpleOpenTag();

        $this->assertEquals('<fieldset>'."\n", $html);
    }

    public function testHandleCsrf()
    {
        $expectedMessages = array(
            'csrf' => array(
                0 => 'message',
            ),
        );

        $element = \Mockery::mock('\Oft\Form\Element\Csrf');
        $element->shouldReceive('getName')
            ->andReturn('csrf');
        $element->shouldReceive('getOptions')
            ->andReturn(array());
        $element->shouldReceive('prepareElement');
        $element->shouldReceive('getAttribute')
            ->with('type')
            ->andReturn('hidden');
        $element->shouldReceive('getMessages')
            ->andReturn(array('message'));
        $element->shouldReceive('setMessages');

        $form = new \Oft\Form\Form();
        $form->remove('csrf');
        $form->getInputFilter()->remove('csrf');
        $form->add($element);

        $formHelper = new \Zend\Form\View\Helper\Form();

        $view = \Mockery::mock('\Oft\View\View');
        $view->shouldReceive('form')
            ->once()
            ->andReturn($formHelper);
        $view->shouldReceive('smartElement')
            ->once()
            ->andReturn('input');

        $helper = new \Oft\View\Helper\SmartForm();
        $helper->setView($view);

        $helper->__invoke($form);

        $messages = $form->getMessages();

        $this->assertEquals($expectedMessages, $messages);
    }

    public function testOpenRowReset()
    {
        $text = new \Zend\Form\Element\Text('text');
        $text->setOption('elm_nl', false);

        $form = new \Oft\Form\Form();
        $form->remove('csrf');
        $form->getInputFilter()->remove('csrf');
        $form->add($text);

        $formHelper = new \Zend\Form\View\Helper\Form();

        $view = \Mockery::mock('\Oft\View\View');
        $view->shouldReceive('form')
            ->twice()
            ->andReturn($formHelper);
        $view->shouldReceive('smartElement')
            ->twice()
            ->andReturn('input');

        $helper = new \Oft\View\Helper\SmartForm();
        $helper->setView($view);

        $html = $helper->__invoke($form);
        $html2 = $helper->__invoke($form);

        $this->assertEquals($html2, $html);
    }
}