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

namespace Oft\Test\View\Resolver;

use Oft\View\Resolver\DirectResolver;
use PHPUnit_Framework_TestCase;

class DirectResolverTest extends PHPUnit_Framework_TestCase
{

    public function testResolverDefaults()
    {
        $resolver = new DirectResolver('/path/to');
        
        $this->assertSame('/path/to' . DIRECTORY_SEPARATOR . 'name' . '.phtml', $resolver->resolve('name'));
    }

    public function testResolverDefaultsTrim()
    {
        $resolver = new DirectResolver('/path/to/');
        
        $this->assertSame('/path/to' . DIRECTORY_SEPARATOR . 'name' . '.phtml', $resolver->resolve('name'));
    }

    public function testResolverWithSuffix()
    {
        $resolver = new DirectResolver('/path/to', 'ext');
        
        $this->assertSame('/path/to' . DIRECTORY_SEPARATOR . 'name' . '.ext', $resolver->resolve('name'));
    }

    public function testResolverWithSuffixTrim()
    {
        $resolver = new DirectResolver('/path/to', '.ext');
        
        $this->assertSame('/path/to' . DIRECTORY_SEPARATOR . 'name' . '.ext', $resolver->resolve('name'));
    }
}
