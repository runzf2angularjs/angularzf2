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

use Oft\Form\Element\Date;
use Oft\Mvc\Application;
use Oft\Util\Functions;
use PHPUnit_Framework_TestCase;
use stdClass;

class DateTest extends PHPUnit_Framework_TestCase
{
    
    public function testConstruct()
    {
        $app = new Application();
        $app->setService('DateFormatter', new stdClass());
        
        Functions::setApp($app);
        
        $dateTime = new Date();
        
        $this->assertEquals('short', $dateTime->getAttribute('dateFormat'));
        $this->assertEquals('none', $dateTime->getAttribute('timeFormat'));
        $this->assertEquals('sql', $dateTime->getAttribute('dateSqlFormat'));
        $this->assertEquals('none', $dateTime->getAttribute('timeSqlFormat'));
        
        Functions::setApp();
    }
    
}
