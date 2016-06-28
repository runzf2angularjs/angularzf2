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

use Mockery;
use Oft\Form\Element\DateTime;
use Oft\Mvc\Application;
use Oft\Util\Functions;
use PHPUnit_Framework_TestCase;
use stdClass;

class DateTimeTest extends PHPUnit_Framework_TestCase
{

    public function testConstruct()
    {
        $app = new Application();
        $app->setService('DateFormatter', new stdClass());
        
        Functions::setApp($app);
        
        $dateTime = new DateTime();
        
        $this->assertInstanceOf('\StdClass', $dateTime->getDateFormatter());
        
        Functions::setApp();
    }
    
    public function testConstructWithOption()
    {
        $dateFormatter = Mockery::mock('\Oft\Date\DateFormatter');
        
        $dateTime = new DateTime('test', array('dateFormatter' => $dateFormatter));
        
        $this->assertInstanceOf('\Oft\Date\DateFormatter', $dateTime->getDateFormatter());
    }
    
    public function testSetOption()
    {
        $dateFormatter = Mockery::mock('\Oft\Date\DateFormatter');
        
        $dateTime = new DateTime('test', array(
            'dateFormatter' => $dateFormatter,
            'type' => 'hidden',
            'dateFormat' => 'short1',
            'timeFormat' => 'medium1',
            'dateSqlFormat' => 'sql1',
            'timeSqlFormat' => 'sql2',
        ));
        
        $this->assertEquals('hidden', $dateTime->getAttribute('type'));
        $this->assertEquals('short1', $dateTime->getAttribute('dateFormat'));
        $this->assertEquals('medium1', $dateTime->getAttribute('timeFormat'));
        $this->assertEquals('sql1', $dateTime->getAttribute('dateSqlFormat'));
        $this->assertEquals('sql2', $dateTime->getAttribute('timeSqlFormat'));
        $this->assertEquals(null, $dateTime->getOption('timeSqlFormat'));
    }
    
    public function testGetInputSpecification()
    {
        $dateFormatter = Mockery::mock('\Oft\Date\DateFormatter');
        
        $dateTime = new DateTime('test', array('dateFormatter' => $dateFormatter,));
        
        $input = $dateTime->getInputSpecification();
        
        $filters = array(
            array('name' => 'Zend\Filter\StringTrim'),
            array('name' => 'Zend\Filter\StripTags'),
        );
        
        $this->assertInstanceOf('\Oft\Validator\DateTime', $input['validators'][0]);
        $this->assertEquals('test', $input['name']);
        $this->assertEquals($filters, $input['filters']);
    }
    
}
