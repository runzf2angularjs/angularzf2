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

namespace Oft\Test\Filter;

class HtmlpurifierTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        \Oft\Filter\Htmlpurifier::$config = \HTMLPurifier_Config::createDefault();
        // Ecriture de cache désactivée pour les tests
        \Oft\Filter\Htmlpurifier::$config->set('Cache.DefinitionImpl', null);
        \Oft\Filter\Htmlpurifier::$config->set('Core.Encoding', 'UTF-8');
    }

    public function testFilter()
    {
        $html = "<html><header></header><body><div>Hello</div></body></html>";

        $filter = new \Oft\Filter\Htmlpurifier();
        $result = $filter->filter($html);

        $this->assertTrue(is_string($result));
        $this->assertContains('<div>Hello</div>', $result);
    }

    public function testFilterDefaultConfig()
    {
        $filter = new \Oft\Filter\Htmlpurifier();
        $defaultConfig = $filter->getConfig();

        $this->assertInstanceOf('HTMLPurifier_Config', $defaultConfig);
    }

    public function testFilterConfig()
    {
        $config = array('k' => 'v');
        
        \Oft\Filter\Htmlpurifier::$config = $config;

        $filter = new \Oft\Filter\Htmlpurifier();
        $result = $filter->getConfig();

        $this->assertEquals($config, $result);
    }
    
    public function testWithoutConfig()
    {
        $filter = new \Oft\Filter\Htmlpurifier();
        $result = $filter->getConfig();

        $this->assertEquals('utf-8', $result->get('Core.Encoding'));
        $this->assertEquals(null, $result->get('Cache.SerializerPath'));
    }
}
