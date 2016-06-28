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

class BreadcrumbTest extends \PHPUnit_Framework_TestCase
{

    protected $breadcrumb;
    
    public function setUp()
    {        
        $translator = new \Zend\I18n\Translator\Translator();
        
        $this->breadcrumb = new \Oft\View\Helper\Breadcrumb();
        $this->breadcrumb->setTranslator($translator);
        
    }

    public function testAddLink()
    {
        $this->breadcrumb->__invoke('test', 'href');
        $rail = (string)$this->breadcrumb;

        $this->assertContains('<a href="href">test</a>', $rail);
    }

    public function testOlList()
    {
        $this->breadcrumb->__invoke('test', 'href');
        $rail = (string)$this->breadcrumb;

        $this->assertContains('<ol class="breadcrumb">', $rail);
    }
}
