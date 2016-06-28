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

namespace Oft\Test\Util;

class ArraysTest extends \PHPUnit_Framework_TestCase
{

    public function testMergeConfig()
    {
        $array1 = array(
            'var' => 'value',
        );
        $array2 = array(
            'var' => 'value2',
            'var2' => 'value',
        );

        $config = \Oft\Util\Arrays::mergeConfig($array1, $array2);

        $this->assertArrayHasKey('var', $config);
        $this->assertArrayHasKey('var2', $config);
        $this->assertEquals('value2', $config['var']);
        $this->assertEquals('value', $config['var2']);
    }

    public function testMergeConfigRecursive()
    {
        $array1 = array(
            'var' => array('value'),
            'var3' => array(
                'key' => 'value',
            ),
            'var4' => array(
                'key' => array(
                    'key' => 'value',
                ),
                'key2' => array(
                    'value'
                )
            )
        );
        $array2 = array(
            'var' => array('value2'),
            'var2' => 'value',
            'var3' => array(
                'key' => 'value2',
                'key2' => 'value',
            ),
            'var4' => array(
                'key' => array(
                    'key' => 'value2',
                ),
                'key2' => array(
                    'value2'
                )
            )
        );

        $config = \Oft\Util\Arrays::mergeConfig($array1, $array2);

        $this->assertInternalType('array', $config);
        $this->assertInternalType('array', $config['var']);
        $this->assertContains('value', $config['var']);
        $this->assertContains('value2', $config['var']);
        $this->assertSame('value', $config['var2']);
        $this->assertInternalType('array', $config['var3']);
        $this->assertSame('value2', $config['var3']['key']);
        $this->assertSame('value', $config['var3']['key2']);
        $this->assertInternalType('array', $config['var4']);
        $this->assertInternalType('array', $config['var4']['key']);
        $this->assertSame('value2', $config['var4']['key']['key']);
        $this->assertInternalType('array', $config['var4']['key2']);
        $this->assertContains('value', $config['var4']['key2']);
        $this->assertContains('value2', $config['var4']['key2']);
    }

    public function testMergeConfigMustThrowsException()
    {
        $this->setExpectedException('RuntimeException');

        $array1 = array(
            'var' => 'value',
        );
        $array2 = array(
            'var' => array('value2'),
        );

        $config = \Oft\Util\Arrays::mergeConfig($array1, $array2);
    }

}
