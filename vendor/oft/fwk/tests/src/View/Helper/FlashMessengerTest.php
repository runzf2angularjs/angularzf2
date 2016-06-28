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

use ArrayObject;
use Oft\View\Helper\FlashMessenger;
use PHPUnit_Framework_TestCase;
use Zend\I18n\Translator\Translator;

class FlashMessengerTest extends PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        $_SESSION = array();
        FlashMessenger::setMessageContainer(null);
    }
    
    public function testGetMessagesContainer()
    {
        $mc = FlashMessenger::getMessagesContainer();
        $this->assertInstanceOf('ArrayObject', $mc);
        $this->assertInstanceOf('ArrayObject', $_SESSION['Oft\View\Helper\FlashMessenger']);
        
        $mc2 = FlashMessenger::getMessagesContainer();
        $this->assertSame($mc, $mc2);
    }

    public function testGetMessagesContainerIfAlreadyInSession()
    {
        $storage = new ArrayObject();
        $_SESSION['Oft\View\Helper\FlashMessenger'] = $storage;
        
        $this->assertSame($storage, FlashMessenger::getMessagesContainer());
    }
    
    public function testInvokeToAdd()
    {
        $storage = new ArrayObject();
        $_SESSION['Oft\View\Helper\FlashMessenger'] = $storage;
        
        $translator = new Translator();
        
        $fm = new FlashMessenger();
        $fm->setTranslator($translator);
        $fm->__invoke('test');
        
        $this->assertArrayHasKey(0, $storage->getArrayCopy());
        $this->assertTrue(is_array($storage[0]));
        $this->assertSame('info', $storage[0][0]);
        $this->assertSame('test', $storage[0][1]);
    }
    
    public function testInvokeToDisplay()
    {
        $storage = new ArrayObject();
        $_SESSION['Oft\View\Helper\FlashMessenger'] = $storage;
        
        $translator = new Translator();
        
        $fm = new FlashMessenger();
        $fm->setTranslator($translator);
        $fm->__invoke('test');
        
        $content = $fm->__invoke();
        
        $this->assertContains('test</div>', $content);
    }

}
