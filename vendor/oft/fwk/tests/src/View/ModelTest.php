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

namespace Oft\Test\View;

use Oft\View\Model;
use PHPUnit_Framework_TestCase;

class ModelTest extends PHPUnit_Framework_TestCase
{

    public function testInit()
    {
        $model = new Model(array('test' => 12));
        
        $this->assertSame(12, $model->test);
        $this->assertSame(12, $model['test']);
    }

    public function testMerge()
    {
        $model = new Model(array('test' => 12));

        $ret = $model->merge(array('test' => 34, 'test2' => 1));

        $this->assertSame($model, $ret);
        $this->assertSame(34, $model['test']);
        $this->assertSame(1, $model['test2']);
    }

    /**
     * Tests PHP internals
     */
    public function testExchangeArrayAndGetArrayCopy()
    {
        $array2 = array('test2' => 34);

        $model = new Model(array('test' => 12));

        $array1 = $model->exchangeArray($array2);

        $this->assertSame(array('test' => 12), $array1);
        $this->assertSame(array('test2' => 34), $model->getArrayCopy());
    }

    /**
     * Tests PHP internals
     */
    public function testGetIterator()
    {
        $model = new Model(array('test' => 12));

        $iterator = $model->getIterator();

        $this->assertInstanceOf('ArrayIterator', $iterator);

        $count = 0;
        foreach ($iterator as $key => $value) {
            $count ++;
            if ($count > 1) {
                $this->fail('Only one iteration is supposed');
            }

            $this->assertSame('test', $key);
            $this->assertSame(12, $value);
        }
    }
    
    /**
     * Tests PHP internals
     */
    public function testDirectIteration()
    {
        $model = new Model(array('test' => 12));

        $count = 0;
        foreach ($model as $key => $value) {
            $count ++;
            if ($count > 1) {
                $this->fail('Only one iteration is supposed');
            }

            $this->assertSame('test', $key);
            $this->assertSame(12, $value);
        }
    }
}
