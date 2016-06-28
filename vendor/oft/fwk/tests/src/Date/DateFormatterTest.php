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

namespace Oft\Test\Date;

use DateTime;
use IntlDateFormatter;
use Oft\Date\DateFormatter;
use Oft\Mvc\Application;
use PHPUnit_Framework_TestCase;
use RuntimeException;
use Zend\I18n\Translator\Translator;

class DateFormatterTest extends PHPUnit_Framework_TestCase
{

    protected $dateFormatter;
    
    protected function setUp()
    {
        $config = array(
            'date' => array(
                'timezone' => null
            )
        );
        
        $translator = new Translator();
        $translator->setLocale('fr');
        
        $app = new Application($config);
        
        $app->setService('Translator', $translator);
        
        $this->dateFormatter = new DateFormatter($app);
    }

    public function testCreateService()
    {
        $this->assertInstanceOf('\Oft\Date\DateFormatter', $this->dateFormatter);
    }

    public function testGetLocale()
    {
        $date = $this->dateFormatter;

        $expected = 'en';
        $date->setLocale($expected);

        $actual = $date->getLocale();

        $this->assertEquals($expected, $actual);
    }

    public function testGetTimezone()
    {
        $date = $this->dateFormatter;

        $expected = 'Europe/Paris';
        $date->setTimezone($expected);

        $actual = $date->getTimezone();

        $this->assertEquals($expected, $actual);
    }
    
    public function testFormatDatetimeShortFr()
    {
        $date = $this->dateFormatter;
        
        $formatedDate = $date->format(new DateTime('31-12-2015'));
        
        $this->assertEquals('31/12/15', $formatedDate);
    }
    
    public function testFormatDatetimeShortEn()
    {
        $date = $this->dateFormatter;
        
        $date->setLocale('en');
        
        $formatedDate = $date->format(new DateTime('31-12-2015'));
        
        $this->assertEquals('12/31/15', $formatedDate);
    }
    
    public function testFormatDateSql()
    {
        $date = $this->dateFormatter;
        
        $date->setLocale('en');
        
        $formatedDate = $date->format(new DateTime('8/21/12'), 'sql');
        
        $this->assertEquals('2012-08-21', $formatedDate);
    }
    
    public function testFormatDateSqlTime()
    {
        $date = $this->dateFormatter;
        
        $date->setLocale('en');
        
        $formatedDate = $date->format(new DateTime('8/21/12 12:15:17'), 'none', 'sql');
        
        $this->assertEquals('12:15:17', $formatedDate);
    }
    
    public function testFormatDateSqlDateTime()
    {
        $date = $this->dateFormatter;
        
        $date->setLocale('en');
        
        $formatedDate = $date->format(new DateTime('8/21/12 12:15:17'), 'sql', 'sql');
        
        $this->assertEquals('2012-08-21 12:15:17', $formatedDate);
    }
    
    public function testFormatDateLongFr()
    {
        $date = $this->dateFormatter;
        
        $formatedDate = $date->format(new DateTime('8/21/12'), IntlDateFormatter::FULL);
        
        $this->assertEquals('mardi 21 août 2012', $formatedDate);
    }
    
    public function testFormatDateTimeMedium()
    {
        $date = $this->dateFormatter;
        
        $formatedDate = $date->format(new DateTime('31-12-2015 12:15:17'), null, 'medium');
        
        $this->assertEquals('31/12/15 12:15:17', $formatedDate);
    }
    
    public function testFormatDateTimeShort()
    {
        $date = $this->dateFormatter;
        
        $formatedDate = $date->format(new DateTime('31-12-2015 12:15:17'), null, 'short');
        
        $this->assertEquals('31/12/15 12:15', $formatedDate);
    }
    
    /**
     * @expectedException RuntimeException
     */
    public function testFormatDateException()
    {
        $date = $this->dateFormatter;
        
        $formatedDate = $date->format(new DateTime('31-12-2015 12:15:17'), 'test');
        
        $this->assertEquals('31/12/15 12:15', $formatedDate);
    }
    
    public function testGenerateDatetimeFromTimestamp()
    {
        $date = $this->dateFormatter;
        
        $date->setTimezone('Europe/Paris');
        
        $timestamp = time();
        
        $formatedDate = $date->generateDateTime($timestamp);
        
        $this->assertEquals($timestamp, $formatedDate->getTimestamp());
    }
    
    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Bad date format
     */
    public function testGenerateDatetimeFromTimestampException()
    {
        $date = $this->dateFormatter;
        
        $date->setTimezone('Europe/Paris');
                
        $timestamp =  111111111111111111111111111;
        
        $date->generateDateTime($timestamp);        
    }
    
    /**
     * @expectedException RuntimeException
     */
    public function testGenerateDateTimeError()
    {
        $date = $this->dateFormatter;
        
        $date->generateDateTime('test');
    }
    
    public function testGenerateDatetime()
    {
        $date = $this->dateFormatter;
        
        $datetime = $date->generateDateTime('20/04/2015');
        
        $formatedDate = $date->format($datetime);
        
        $this->assertEquals('20/04/15', $formatedDate);
    }
    
    public function testGenerateDatetimeLong()
    {
        $date = $this->dateFormatter;
        
        $datetime = $date->generateDateTime('20 avril 2015', 'long');
        
        $formatedDate = $date->format($datetime, 'long');
        
        $this->assertEquals('20 avril 2015', $formatedDate);
    }
    
    public function testGenerateDatetimeGregorian()
    {
        $date = $this->dateFormatter;
        
        $datetime = $date->generateDateTime('20 avril 2015', 'gregorian');
        
        $formatedDate = $date->format($datetime, 'gregorian');
        
        $this->assertEquals('20 avril 2015', $formatedDate);
    }
    
    public function testGenerateDatetimeFull()
    {
        $date = $this->dateFormatter;
        
        $datetime = $date->generateDateTime('lundi 20 avril 2015', 'full');
        
        $formatedDate = $date->format($datetime, 'full');
        
        $this->assertEquals('lundi 20 avril 2015', $formatedDate);
    }
    
    /**
     * @expectedException RuntimeException
     */
    public function testGenerateDatetimeFullBadLanguage()
    {
        $date = $this->dateFormatter;
        
        $date->setLocale('en');
        
        $datetime = $date->generateDateTime('lundi 20 avril 2015', 'full');
    }
    
    /**
     * @expectedException RuntimeException
     */
    public function testGenerateDatetimeFullBadFormat()
    {
        $date = $this->dateFormatter;
        
        $datetime = $date->generateDateTime('20 avril 2015', 'traditional');
    }
    
    /**
     * @expectedException RuntimeException
     */
    public function testGenerateDatetimeFullBadParameter()
    {
        $date = $this->dateFormatter;
        
        $dateFormat = new DateTime();
        
        $datetime = $date->generateDateTime('20 avril 2015', $dateFormat);
    }

}
