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

namespace Oft\Test\Entity;

use Oft\Entity\BaseEntity;
use PHPUnit_Framework_TestCase;

class BaseEntityTest extends PHPUnit_Framework_TestCase
{
    public function testStrictModeExchangeArray()
    {
        $baseEntity = new BaseEntity(array(
            'a' => 1,
            'b' => 2
        ), true); // Strict

        $exchanged = $baseEntity->exchangeArray(array('b' => 3, 'c' => 4));

        $this->assertSame(array('a' => 1, 'b' => 2), $exchanged);
        $this->assertSame(array('a' => null, 'b' => 3), $baseEntity->getArrayCopy());
    }

    public function testStrictModeOffsetSet()
    {
        $baseEntity = new BaseEntity(array('a' => 1), true); // Strict

        $baseEntity['a'] = 2;

        $this->assertNull($baseEntity['b']); // Strict : NULL
    }

    public function testStrictModeMagicSet()
    {
        $baseEntity = new BaseEntity(array('a' => 1), true);

        $baseEntity->a = 2;

        $this->assertNull($baseEntity->b); // Strict : NULL
    }

    public function testExchangeArray()
    {
        $baseEntity = new BaseEntity(array(
            'a' => 1,
            'b' => 2
        ));

        $exchanged = $baseEntity->exchangeArray(array('b' => 3, 'c' => 4));

        $this->assertSame(array('a' => 1, 'b' => 2), $exchanged);
        $this->assertSame(array('a' => null, 'b' => 3, 'c' => 4), $baseEntity->getArrayCopy());
    }

    public function testGetArrayCopy()
    {
        $baseEntity = new BaseEntity(array(
            'a' => 1,
            'b' => 2
        ));
        $this->assertSame(array('a' => 1, 'b' => 2), $baseEntity->getArrayCopy());
    }

    public function testOffsetExists()
    {
        $baseEntity = new BaseEntity(array('a' => 1));

        $this->assertTrue(isset($baseEntity['a']));
        $this->assertFalse(isset($baseEntity['c']));
    }

    public function testOffsetGet()
    {
        $baseEntity = new BaseEntity(array('a' => 1));

        $this->assertSame(1, $baseEntity['a']);
        $this->assertSame(null, $baseEntity['c']);
    }

    public function testOffsetSet()
    {
        $baseEntity = new BaseEntity(array('a' => 1));

        $baseEntity['a'] = 2;
        $baseEntity['b'] = 3;

        $this->assertSame(2, $baseEntity['a']);
        $this->assertSame(3, $baseEntity['b']);
    }

    public function testOffsetUnset()
    {
        $baseEntity = new BaseEntity(array('a' => 1));
        
        unset($baseEntity['a']);

        $this->assertSame(null, $baseEntity['a']);
    }

    public function testMagicGet()
    {
        $baseEntity = new BaseEntity(array('a' => 1));

        $this->assertSame(1, $baseEntity->a);
        $this->assertSame(null, $baseEntity->b);
    }

    public function testMagicSet()
    {
        $baseEntity = new BaseEntity(array('a' => 1));
        
        $baseEntity->a = 2;
        $baseEntity->b = 3;

        $this->assertSame(2, $baseEntity->a);
        $this->assertSame(3, $baseEntity->b);
    }

    public function testMagicUnset()
    {
        $baseEntity = new BaseEntity(array('a' => 1));
        
        unset($baseEntity->a);

        $this->assertSame(null, $baseEntity->a);
    }

    public function testMagicIsset()
    {
        $baseEntity = new BaseEntity(array('a' => 1, 'b' => null));

        $this->assertTrue(isset($baseEntity->a));
        $this->assertFalse(isset($baseEntity->b));
        $this->assertFalse(isset($baseEntity->c));
    }

    public function testGetUpdatedFieldsWithNoMod()
    {
        $baseEntity = new BaseEntity(array('a' => 1, 'b' => 2));

        $this->assertSame(array(), $baseEntity->getUpdatedFields());
    }

    public function testGetUpdatedFieldsWithArrayKeyExchange()
    {
        $baseEntity = new BaseEntity(array('a' => 1, 'b' => 2));

        $baseEntity->exchangeArray(array('a' => 2, 'b' => 2, 'c' => 4));

        $this->assertSame(array('a' => 2, 'c' => 4), $baseEntity->getUpdatedFields());
    }

    public function testGetUpdatedFieldsWithMagicSet()
    {
        $baseEntity = new BaseEntity(array('a' => 1, 'b' => 2));

        $baseEntity->a = 2;
        $baseEntity->b = 2;
        $baseEntity->c = 2;

        $this->assertSame(array('a' => 2, 'c' => 2), $baseEntity->getUpdatedFields());
    }

    public function testGetUpdatedFieldsWithArrayAccessSet()
    {
        $baseEntity = new BaseEntity(array('a' => 1, 'b' => 2));

        $baseEntity['a'] = 2;
        $baseEntity['b'] = 2;
        $baseEntity['c'] = 2;

        $this->assertSame(array('a' => 2, 'c' => 2), $baseEntity->getUpdatedFields());
    }

    public function testIterator()
    {
        $array = array('a' => 1, 'b' => 2);
        $baseEntity = new BaseEntity($array);

        $count = 0;
        foreach ($baseEntity as $key => $val) {
            $count ++;
            $this->assertArrayHasKey($key, $array);
            $this->assertSame($val, $array[$key]);
        }

        $this->assertEquals(2, $count);
    }

    /* API style usage */
    public function testFilterInputForApis()
    {        
        $validator = new \Zend\I18n\Validator\IsInt();
        
        $validatorChain = new \Zend\Validator\ValidatorChain();
        $validatorChain->attach($validator);
        
        $a = new \Zend\InputFilter\Input('a');
        $b = new \Zend\InputFilter\Input('b');
        
        $a->setValidatorChain($validatorChain);
        $b->setValidatorChain($validatorChain);
        
        $fi = new \Zend\InputFilter\InputFilter();
        $fi->add($a);
        $fi->add($b);

        $baseEntity = new BaseEntity(array('a' => 1, 'b' => 2));

        $fi->setData($baseEntity);

        $this->assertTrue($fi->isValid());
        $this->assertEquals(array('a' => 1, 'b' => 2), $fi->getValues());

        $baseEntity->exchangeArray($fi->getValues());

        $this->assertEquals(array('a' => 1, 'b' => 2), $baseEntity->getArrayCopy());
        $this->assertSame(array(), $baseEntity->getUpdatedFields());
    }
}
