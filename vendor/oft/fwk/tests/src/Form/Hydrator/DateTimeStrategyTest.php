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

namespace Oft\Test\Form\Hydrator;

use Oft\Date\DateFormatter;
use Oft\Form\Element\DateTime;
use Oft\Form\Hydrator\DateTimeStrategy;
use Oft\Mvc\Application;
use PHPUnit_Framework_TestCase;
use Zend\I18n\Translator\Translator;

class DateTimeStrategyTest extends PHPUnit_Framework_TestCase
{

    public function getDateTimeElement()
    {
        $app = new Application(array(
            'date' => array(
                'timezone' => 'Europe/Paris'
            )
        ));
        $app->setService('Translator', new Translator());
        $app->get('Translator')->setLocale('fr_FR');

        $dateFormatter = new DateFormatter($app);

        $dateTime = new DateTime('test', array(
            'dateFormatter' => $dateFormatter
        ));

        return $dateTime;
    }

    public function testExtractWhenEmpty()
    {
        $dateTime = $this->getDateTimeElement();
        
        $dateTimeStrategy = new DateTimeStrategy($dateTime);

        $result = $dateTimeStrategy->extract('');

        $this->assertNull($result);
    }
    
    public function testExtractWhenValid()
    {
        $dateTime = $this->getDateTimeElement();

        $dateTimeStrategy = new DateTimeStrategy($dateTime);

        $timestamp = '1434379945';

        $result = $dateTimeStrategy->extract($timestamp);

        $this->assertSame('15/06/15 16:52:25', $result);
    }

    public function testExtractWhenInValid()
    {
        $dateTime = $this->getDateTimeElement();

        $dateTimeStrategy = new DateTimeStrategy($dateTime);

        $timestamp = 'test';

        $result = $dateTimeStrategy->extract($timestamp);

        $this->assertSame('test', $result);
    }

    public function testHydrateWhenEmpty()
    {
        $dateTime = $this->getDateTimeElement();
        
        $dateTimeStrategy = new DateTimeStrategy($dateTime);

        $result = $dateTimeStrategy->hydrate('');

        $this->assertNull($result);
    }

    public function testHydrateWhenNotEmpty()
    {
        $dateTime = $this->getDateTimeElement();

        $dateTimeStrategy = new DateTimeStrategy($dateTime);

        $timestamp = '1434379945';

        $result = $dateTimeStrategy->hydrate($timestamp);

        $this->assertSame("2015-06-15 16:52:25", $result);
    }

    public function testHydrateWhenInvalid()
    {
        $dateTime = $this->getDateTimeElement();

        $dateTimeStrategy = new DateTimeStrategy($dateTime);

        $timestamp = 'test';

        $result = $dateTimeStrategy->hydrate($timestamp);

        $this->assertSame("test", $result);
    }

}
