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

class SmartElementTest extends \PHPUnit_Framework_TestCase
{
    protected $testClass;
    protected $reflection;

    public function setUp()
    {
        $this->testClass = new \Oft\View\Helper\SmartElement();
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

    public function testOptionsNotNull()
    {
        $helper = new \Oft\View\Helper\SmartElement();
        $options = $helper->getOptions();

        $this->assertNotNull($options);
    }

    public function testMergeOptionsAdd()
    {
        $helper = new \Oft\View\Helper\SmartElement();
        $options = array('new_option' => true);

        $helper->mergeOptions($options);
        $optionsMerged = $helper->getOptions();

        $keyExists = \array_key_exists('new_option', $optionsMerged);

        $this->assertTrue($keyExists);
    }

    public function testMergeOptionsOverwrite()
    {
        $helper = new \Oft\View\Helper\SmartElement();
        $newSeparator = 'X';

        $helper->mergeOptions(array('separator' => $newSeparator));
        $optionsMerged = $helper->getOptions();
        $separatorMerged = $optionsMerged['separator'];

        $this->assertEquals($newSeparator, $separatorMerged);
    }

    public function testHasOptionTrue()
    {
        $helper = new \Oft\View\Helper\SmartElement();

        $helper->mergeOptions(array('legend' => 'légende'));

        $this->assertTrue($helper->hasOption('legend'));
    }

    public function testHasOptionFalse()
    {
        $helper = new \Oft\View\Helper\SmartElement();

        $this->assertFalse($helper->hasOption('not-an-option'));
    }

    public function testMergeElementMapAttribs()
    {
        $helper = new \Oft\View\Helper\SmartElement();

        $mapAttribs = array(
            'elm_size' => 5,
            'elm_nl' => false,
            'elm_align' => 'center',
            'elm_prefix' => 3,
            'elm_suffix' => 4,
            'label_size' => 4,
            'label_nl' => true,
            'label_align' => 'left',
            'label_prefix' => 3,
            'label_suffix' => 5,
        );
        $element = new \Zend\Form\Element\Text('text', $mapAttribs);

        $getMapAttribs = $helper->mergeElementMapAttribs($element, $mapAttribs);

        $this->assertEquals($mapAttribs, $getMapAttribs);
    }

    public function testIsSubmitElement()
    {
        $helper = new \Oft\View\Helper\SmartElement();

        $element = \Mockery::mock('\Zend\Form\Element\Submit');
        $isSubmit = $helper->isSubmitElement($element);

        $this->assertTrue($isSubmit);
    }
    
    public function testIsSubmitElementButtonWithAttr()
    {
        $helper = new \Oft\View\Helper\SmartElement();

        $element = \Mockery::mock('\Zend\Form\Element\Button');
        $element->shouldReceive('getAttribute')
            ->once()
            ->withArgs(array('type'))
            ->andReturn('SuBmiT');

        $isSubmit = $helper->isSubmitElement($element);

        $this->assertTrue($isSubmit);
    }

    public function testIsSubmitElementNotSubmit()
    {
        $helper = new \Oft\View\Helper\SmartElement();

        $element = \Mockery::mock('\Zend\Form\Element\Text');
        $element->shouldReceive('getAttribute')
            ->once()
            ->withArgs(array('type'))
            ->andReturn('Text');

        $isSubmit = $helper->isSubmitElement($element);

        $this->assertFalse($isSubmit);
    }

    public function testIsResetElement()
    {
        $helper = new \Oft\View\Helper\SmartElement();

        $element = \Mockery::mock('\Zend\Form\Element\Button');
        $element->shouldReceive('getAttribute')
            ->once()
            ->withArgs(array('type'))
            ->andReturn('ReseT');

        $isReset = $helper->isResetElement($element);

        $this->assertTrue($isReset);
    }

    public function testIsResetElementFalse()
    {
        $helper = new \Oft\View\Helper\SmartElement();

        $element = \Mockery::mock('\Zend\Form\Element\Text');
        $element->shouldReceive('getAttribute')
            ->once()
            ->withArgs(array('type'))
            ->andReturn('Text');

        $isReset = $helper->isResetElement($element);

        $this->assertFalse($isReset);
    }

    public function testElementHasMessages()
    {
        $helper = new \Oft\View\Helper\SmartElement();

        $messages = array('ex' => 'ex');
        $element = new \Zend\Form\Element\Text('text');
        $element->setMessages($messages);

        $hasMessages = $helper->elementHasMessages($element);

        $this->assertTrue($hasMessages);
    }

    

    public function testGetLabelTagNull()
    {
        $helper = new \Oft\View\Helper\SmartElement();
        $element = new \Zend\Form\Element();
        $required = true;

        $element->setLabel('');

        $tag = $helper->getLabelTag($element, $required);

        $this->assertEquals('', $tag);
    }

    public function testGetLabelTag()
    {
        $el = new \Zend\Form\Element\Text('test');
        $el->setLabel('label');
        
        $translator = new \Zend\I18n\Translator\Translator();
        
        $helperForm = new \Zend\Form\View\Helper\Form();
        $helperLabel = new \Zend\Form\View\Helper\FormLabel();
        
        $view = \Mockery::mock('\Oft\View\View');
        $view->shouldReceive('form')
            ->andReturn($helperForm);
        $view->shouldReceive('formLabel')
            ->andReturn($helperLabel);
        
        $helper = new \Oft\View\Helper\SmartElement();
        $helper->mergeOptions();
        $helper->setTranslator($translator);
        $helper->setView($view);
        
        $html = $helper->getLabelTag($el);
        
        $this->assertEquals('<label for="test">label&nbsp;:</label>', $html);
    }

    public function testGetDescriptionTag()
    {
        $helper = new \Oft\View\Helper\SmartElement();
        $element = new \Zend\Form\Element\Text('field');

        $descText = 'test-description';
        $element->setOption('description', $descText);

        $tag = $helper->getDescriptionTag($element);

        $isDescTag = (\strpos($tag, '<p ') !== false);
        $isDescText = (\strpos($tag, $descText) !== false);

        $this->assertTrue($isDescTag);
        $this->assertTrue($isDescText);
    }

    public function testGetMessageTag()
    {
        $helper = new \Oft\View\Helper\SmartElement();
        $element = new \Zend\Form\Element\Text('field');

        $elementMessages = array(
            'msg-1' => 'test-message-1',
            'msg-2' => 'test-message-2'
        );
        $element->setMessages($elementMessages);

        $tag = $helper->getMessagesTag($element);

        $isMsgTag = (\strpos($tag, '<p ') !== false);
        $isMsgText1 = (\strpos($tag, $elementMessages['msg-1']) !== false);
        $isMsgText2 = (\strpos($tag, $elementMessages['msg-2']) !== false);

        $this->assertTrue($isMsgTag);
        $this->assertTrue($isMsgText1);
        $this->assertTrue($isMsgText2);
    }

    public function testGetCssClass()
    {
        $helper = new \Oft\View\Helper\SmartElement();

        $size = $helper->getCssClass('size', 4);
        $prefix = $helper->getCssClass('prefix', 4);
        $push = $helper->getCssClass('push', 4);
        $align = $helper->getCssClass('align', 'center');
        $errorFeedback = $helper->getCssClass('error-feedback');
        $not = $helper->getCssClass('non-gere');

        $this->assertEquals(' col-xs-12 col-sm-4 ', $size);
        $this->assertEquals(' col-sm-offset-4 ', $prefix);
        $this->assertEquals(' col-sm-push-4 ', $push);
        $this->assertEquals(' text-center ', $align);
        $this->assertEquals(' has-error ', $errorFeedback);
        $this->assertEquals('', $not);
    }
    
    public function testInvokeHidden()
    {
        $el = new \Zend\Form\Element\Hidden('hidden');
        $el->setValue('test');
        
        $pluginManager = new \Zend\View\HelperPluginManager();
        $pluginManager->setServiceLocator(new \Oft\Mvc\Application());
        
        $helperConfig = new \Zend\Form\View\HelperConfig();
        $helperConfig->configureServiceManager($pluginManager);
                
        $view = new \Oft\View\View();
        $view->setHelperPluginManager($pluginManager);
        
        $helper = new \Oft\View\Helper\SmartElement();
        $helper->setView($view);
        
        $html = $helper->__invoke($el);
        
        $this->assertEquals('<input type="hidden" name="hidden" value="test">'."\n", $html);
    }
    
    public function testInvokeSubmit()
    {
        $expected = "<div class=\" col-xs-12 col-sm-6  col-sm-offset-6  text-left  text-align-responsive\">\n"
            . "<input type=\"submit\" name=\"button\" id=\"button\" class=\"btn&#x20;btn-primary\" value=\"test\">\n"
            . "</div>";
        
        $el = new \Zend\Form\Element\Submit('button');
        $el->setValue('test');
        
        $pluginManager = new \Zend\View\HelperPluginManager();
        $pluginManager->setServiceLocator(new \Oft\Mvc\Application());
        
        $helperConfig = new \Zend\Form\View\HelperConfig();
        $helperConfig->configureServiceManager($pluginManager);
                
        $view = new \Oft\View\View();
        $view->setHelperPluginManager($pluginManager);
        
        $helper = new \Oft\View\Helper\SmartElement();
        $helper->setView($view);
        
        $html = $helper->__invoke($el);
                
        $this->assertEquals($expected, $html);
    }
    
    public function testInvokeText()
    {
        $expected = "<div class=\" col-xs-12 col-sm-6  text-left  text-align-responsive\">\n"
            . "<input type=\"text\" name=\"button\" id=\"button\" class=\"form-control\" value=\"test\">\n"
            . "</div>";
        
        $el = new \Zend\Form\Element\Text('button');
        $el->setValue('test');
        
        $pluginManager = new \Zend\View\HelperPluginManager();
        $pluginManager->setServiceLocator(new \Oft\Mvc\Application());
        
        $helperConfig = new \Zend\Form\View\HelperConfig();
        $helperConfig->configureServiceManager($pluginManager);
                
        $view = new \Oft\View\View();
        $view->setHelperPluginManager($pluginManager);
        
        $helper = new \Oft\View\Helper\SmartElement();
        $helper->setView($view);
        
        $html = $helper->__invoke($el);
                
        $this->assertEquals($expected, $html);
    }
    
    /**
     * Fix #585 Exception levée quand le type est File 
     */
    public function testGetInputTagFile()
    {
        $pluginManager = new \Zend\View\HelperPluginManager();
        $pluginManager->setServiceLocator(new \Oft\Mvc\Application());
        
        $helperConfig = new \Zend\Form\View\HelperConfig();
        $helperConfig->configureServiceManager($pluginManager);
                
        $view = new \Oft\View\View();
        $view->setHelperPluginManager($pluginManager);
        
        $helper = new \Oft\View\Helper\SmartElement();
        $helper->setView($view);
        
        $el = new \Zend\Form\Element\File('file');
        
        $html = $helper->getInputTag($el);
        
        $this->assertEquals('<input type="file" name="file">', $html);
    }
    
    public function testMandatorySymbole()
    {
        $element = new \Zend\Form\Element\Text();
        $element->setLabel('text');
        
        $options = array(
            'required' => true,
            'show_mandatory_symbol' => true,
        );
        
        $translator = new \Zend\I18n\Translator\Translator(); 
       
        $pluginManager = new \Zend\View\HelperPluginManager();
        $pluginManager->setServiceLocator(new \Oft\Mvc\Application());
        
        $helperConfig = new \Zend\Form\View\HelperConfig();
        $helperConfig->configureServiceManager($pluginManager);
        
        $view = new \Oft\View\View();
        $view->setHelperPluginManager($pluginManager);
        
        $helper = new \Oft\View\Helper\SmartElement();
        $helper->setView($view);
        $helper->setTranslator($translator);
        $helper->mergeOptions($options);
        
        $html = $helper->getLabelTag($element);
        
        $expected = '<label for="">text&nbsp;<abbr class="required" title="champ obligatoire">*</abbr>&nbsp;:</label>';
        
        $this->assertEquals($expected, $html);
    }
    
    public function testShowErrorFeedback()
    {
        $element = new \Zend\Form\Element\Text();
        $element->setLabel('text');
        $element->setAttribute('class', null);
        $element->setMessages(array('message'));
        
        $options = array(
            'show_error_feedback' => true,
        );
        
        $translator = new \Zend\I18n\Translator\Translator(); 
       
        $pluginManager = new \Zend\View\HelperPluginManager();
        $pluginManager->setServiceLocator(new \Oft\Mvc\Application());
        
        $helperConfig = new \Zend\Form\View\HelperConfig();
        $helperConfig->configureServiceManager($pluginManager);
        
        $view = new \Oft\View\View();
        $view->setHelperPluginManager($pluginManager);
        
        $helper = new \Oft\View\Helper\SmartElement();
        $helper->setView($view);
        $helper->setTranslator($translator);
        $helper->mergeOptions($options);
        
        $html = $helper->getLabelTag($element);
        
        $expected = '<label for="" class="&#x20;control-label&#x20;">text&nbsp;:</label>';
        
        $this->assertEquals($expected, $html);
    }
    
    public function testRadioElement()
    {
        $element = new \Zend\Form\Element\Radio('radio');
        $element->setOption('inline', true);
        $element->setValueOptions(array(
            '0' => 'test0',
            '1' => 'test1',
        ));
        
        $pluginManager = new \Zend\View\HelperPluginManager();
        $pluginManager->setServiceLocator(new \Oft\Mvc\Application());
        
        $helperConfig = new \Zend\Form\View\HelperConfig();
        $helperConfig->configureServiceManager($pluginManager);
        
        $view = new \Oft\View\View();
        $view->setHelperPluginManager($pluginManager);
        
        $helper = new \Oft\View\Helper\SmartElement();
        $helper->setView($view);
        
        $html = $helper->getRadioAndCheckbox($element);
        
        $expected = '<div  class="radio-inline"><input type="radio" name="radio" value="0">test0</div>'
            . '<div  class="radio-inline"><input type="radio" name="radio" value="1">test1</div>';
        
        $this->assertEquals($expected, $html);
    }
    
    public function testMultiCheckboxElement()
    {
        $element = new \Zend\Form\Element\MultiCheckbox('multi');
        $element->setValueOptions(array(
            '0' => 'test0',
            '1' => 'test1',
        ));
        
        $pluginManager = new \Zend\View\HelperPluginManager();
        $pluginManager->setServiceLocator(new \Oft\Mvc\Application());
        
        $helperConfig = new \Zend\Form\View\HelperConfig();
        $helperConfig->configureServiceManager($pluginManager);
        
        $view = new \Oft\View\View();
        $view->setHelperPluginManager($pluginManager);
        
        $helper = new \Oft\View\Helper\SmartElement();
        $helper->setView($view);
        
        $html = $helper->getRadioAndCheckbox($element);
        
        $expected = '<div  class="checkbox"><input type="checkbox" name="multi&#x5B;&#x5D;" value="0">test0</div>'
            . '<div  class="checkbox"><input type="checkbox" name="multi&#x5B;&#x5D;" value="1">test1</div>';
        
        $this->assertEquals($expected, $html);
    }
    
    /**
     * Bug #630
     * Vérification que les options de l'input précédent ne sont pas conservées
     */
    public function testResetOptionsMultipleElement()
    {
        $submit = new \Zend\Form\Element\Submit('submit');
        $submit->setValue('valider');
        
        $text = new \Zend\Form\Element\Text('text');
        $text->setLabel('label');
        
        $pluginManager = new \Zend\View\HelperPluginManager();
        $pluginManager->setServiceLocator(new \Oft\Mvc\Application());
        
        $helperConfig = new \Zend\Form\View\HelperConfig();
        $helperConfig->configureServiceManager($pluginManager);
        
        $view = new \Oft\View\View();
        $view->setHelperPluginManager($pluginManager);
        
        $helper = new \Oft\View\Helper\SmartElement();
        $helper->setView($view);
        
        $translator = new \Zend\I18n\Translator\Translator(); 
        $helper->setTranslator($translator);
        
        $htmlSubmit = $helper->__invoke($submit);
        
        $htmlText = $helper->__invoke($text);
                
        $expectedText = '<div class=" col-xs-12 col-sm-6  text-right  text-align-responsive">' . "\n" .
            '<label for="text">label&nbsp;:</label>' . "\n" .
            '</div>' . "\n" .
            '<div class=" col-xs-12 col-sm-6  text-left  text-align-responsive">' . "\n" .
            '<input type="text" name="text" id="text" class="form-control" value="">' . "\n" .
            '</div>';
        
        $this->assertEquals($expectedText, $htmlText);
    }
}
