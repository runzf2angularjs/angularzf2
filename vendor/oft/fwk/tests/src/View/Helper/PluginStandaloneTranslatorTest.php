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

use Oft\View\Helper\PluginStandaloneTranslator;
use PHPUnit_Framework_TestCase;
use Zend\I18n\Translator\Translator;

class PluginStandaloneTranslatorTest extends PHPUnit_Framework_TestCase
{

    public function testHasTranslator()
    {
        $plugin = new PluginStandaloneTranslator();
        
        $this->assertFalse($plugin->hasTranslator());
        
        $translator = new Translator();
        $plugin->setTranslator($translator, 'test');

        $this->assertTrue($plugin->hasTranslator());
        $this->assertEquals($translator, $plugin->getTranslator());
    }
    
    public function testSetTranslatorEnable()
    {
        $plugin = new PluginStandaloneTranslator();
        $plugin->setTranslator(new Translator());
        $plugin->setTranslatorEnabled(false);
        
        $this->assertEquals(null, $plugin->getTranslator());
    }

    public function testGetTranslatorTextDomain()
    {
        $plugin = new PluginStandaloneTranslator();

        $expected = 'textDomain';
        $plugin->setTranslatorTextDomain($expected);

        $actual = $plugin->getTranslatorTextDomain();

        $this->assertEquals($expected, $actual);
    }

}
