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

namespace Oft\Test\Validator\Db;

use Oft\Validator\Db\RecordExists;
use PHPUnit_Framework_TestCase;

class RecordExistsMock extends RecordExists
{
    public static $queryResult;

    public function query($value)
    {
        return self::$queryResult;
    }
}

class RecordExistsTest extends PHPUnit_Framework_TestCase
{

    public function testIsValidFound()
    {
        $options = array(
            'table' => 'table',
            'field' => 'field',
        );
        
        RecordExistsMock::$queryResult = true;

        $validator = new RecordExistsMock($options);
        $result = $validator->isValid('value');

        $this->assertTrue($result);
    }

    public function testIsValidNotFound()
    {
        $options = array(
            'table' => 'table',
            'field' => 'field',
        );

        RecordExistsMock::$queryResult = false;

        $validator = new RecordExistsMock($options);
        $result = $validator->isValid('value');

        $this->assertFalse($result);
        $this->assertCount(1, $validator->getMessages());
    }

}
