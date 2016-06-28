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

class CuidTest extends \PHPUnit_Framework_TestCase
{
    public function testIsValid ()
    {
        $cuids = array(
            'GUEST'     => true,
            'ABCD1234'  => true,
            'abcd1234'  => true,
            'abcde1234' => false,
            'abcd123'   => false,
            'abcdefgh'  => false,
            '12345678'  => false,
        );

        $validator = new \Oft\Validator\Cuid();
        foreach ($cuids as $cuid => $expected) {
            $this->assertSame($expected, $validator->isValid($cuid));
        }
    }

    public function testError()
    {
        $cuid = 'invalidcuid';
        $validator = new \Oft\Validator\Cuid();
        $this->assertFalse($validator->isValid($cuid));
        $messages = $validator->getMessages();
        $this->assertInternalType('array', $messages);
        $this->assertEquals(1, count($messages));
        $this->assertArrayHasKey(\Oft\Validator\Cuid::CUID_INVALID, $messages);

        $templates = $validator->getMessageTemplates();
        $this->assertArrayHasKey(\Oft\Validator\Cuid::CUID_INVALID, $templates);

        $this->assertEquals(
            $messages[\Oft\Validator\Cuid::CUID_INVALID],
            $templates[\Oft\Validator\Cuid::CUID_INVALID]
        );
    }
}

