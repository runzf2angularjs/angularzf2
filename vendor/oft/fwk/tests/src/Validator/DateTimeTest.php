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

namespace Oft\Test\Validator;

class DateTimeTest extends \PHPUnit_Framework_TestCase
{
    public function testIsValidSql()
    {
        $config = array(
            'date' => array(
                'timezone' => 'Europe/Paris',
            ),
        );
        
        $translator = new \Zend\I18n\Translator\Translator();
        $translator->setLocale('fr');
        
        $app = new \Oft\Mvc\Application($config);
        $app->setService('Translator', $translator);
        
        $dateFormmater = new \Oft\Date\DateFormatter($app);
        
        $validator = new \Oft\Validator\DateTime($dateFormmater, 'short', 'sql', 'medium', 'sql');
        
        $this->assertTrue($validator->isValid('2015-06-17 09:50:30'));
        $this->assertFalse($validator->isValid('2015-06-17 09:50'));
        $this->assertFalse($validator->isValid('TEST'));
        $this->assertFalse($validator->isValid(null));
        $this->assertTrue($validator->isValid(123456));
        $this->assertTrue($validator->isValid('17/06/15 09:50:30'));
        $this->assertFalse($validator->isValid('17/06/15 09:50'));
        $this->assertFalse($validator->isValid('6/17/15 09:50:30'));
        
        $this->assertEquals(array('dateInvalid' => 'Date has an invalid format'), $validator->getMessages());
    }
}

