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

use Oft\Date\DateFormatter as DateFormatter2;
use Oft\Mvc\Application;
use Oft\View\Helper\DateFormatter;
use Oft\View\View;
use PHPUnit_Framework_TestCase;
use Zend\I18n\Translator\Translator;

class DateFormatterTest extends PHPUnit_Framework_TestCase
{
    protected $helper;
    
    protected function setUp()
    {
        $config = array(
            'date' => array(
                'timezone' => 'Europe/Paris',
            ),
        );
        
        $translator = new Translator();
        $translator->setLocale('fr');
        
        $app = new Application($config);
        $app->setService('Translator', $translator);
        
        $dateFormatter = new DateFormatter2($app);
        
        $app->setService('DateFormatter', $dateFormatter);
        
        $view = new View();
        $view->setApplication($app);
        
        $this->helper = new DateFormatter();
        $this->helper->setView($view);
    }
    
    public function testDateFormatterDatetimeSql()
    {
        $date = '2015-06-16 14:47:45';
        
        $expected = '16/06/15 14:47:45';
        
        $result = $this->helper->__invoke($date);
        
        $this->assertEquals($expected, $result);
    }
    
    public function testDateFormatterDateSql()
    {
        $date = '2015-06-16';
        
        $expected = '16/06/15';
        
        $result = $this->helper->__invoke($date, 'short', 'none', 'sql', 'none');
        
        $this->assertEquals($expected, $result);
    }
    
    public function testDateFormatterTimeSql()
    {
        $date = '14:47:45';
        
        $expected = '14:47:45';
        
        $result = $this->helper->__invoke($date, 'none', 'medium', 'none', 'sql');
        
        $this->assertEquals($expected, $result);
    }
    
    public function testDateFormatterDatetimeWrongFormat()
    {
        $date = '2015-06-16 14:47:45';
                
        $result = $this->helper->__invoke($date, 'short', 'medium', 'none', 'sql');
        
        $this->assertEquals($date, $result);
    }
}