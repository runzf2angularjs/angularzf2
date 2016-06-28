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

namespace Oft\Test\functions;

use Mockery;
use Oft\Mvc\Application;
use Oft\Util\Functions;
use PHPUnit_Framework_TestCase;

class TranslatorTest extends PHPUnit_Framework_TestCase
{

    protected function setUp()
    {        
        $translator = Mockery::mock('\Oft\Service\Provider\Translator');
        $translator->shouldReceive('translate')
            ->with('test')
            ->andReturn('testTranslated');
        $translator->shouldReceive('translate')
            ->with('test %1$s %2$s')
            ->andReturn('testTranslated %1$s %2$s');
                
        $app = new Application();
        $app->setService('Translator', $translator);

        Functions::setApp($app);
    }
    
    protected function tearDown()
    {
        Functions::setApp(null);
    }

    public function testTranslate()
    {
        $value = 'test';
        
        $translation = __($value);
        
        $this->assertEquals('testTranslated', $translation);
    }
    
    public function testTranslateWithParams()
    {
        $value = 'test %1$s %2$s';
        
        $translation = __($value, 'test1', 'test2');
        
        $this->assertEquals('testTranslated test1 test2', $translation);
    }

}
