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

namespace Oft\Test\Debug;

use Oft\Debug\Disabled;
use PHPUnit_Framework_TestCase;

class DisabledTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Disabled
     */
    protected $disabled;

    public function setUp()
    {
        $this->disabled = new Disabled();
    }

    public function testAddException()
    {
        $e = new \Exception();

        $result = $this->disabled->addException($e);

        $this->assertNull($result);
    }

    public function testAddMessage()
    {
        $result = $this->disabled->addMessage('test', 'type');

        $this->assertNull($result);
    }

    public function testIsDebug()
    {
        $this->assertFalse($this->disabled->isDebug());
    }

    public function testDump()
    {
        $result = $this->disabled->dump('test', 'title', true);

        $this->assertNull($result);
    }
}
 