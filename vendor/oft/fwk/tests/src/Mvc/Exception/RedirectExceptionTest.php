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

namespace Oft\Test\Mvc\Exception;

class RedirectExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetUrl()
    {
        $e = new \Oft\Mvc\Exception\RedirectException('/path/to');

        $headers = $e->getHeaders();

        $this->assertInternalType('array', $headers);
        $this->assertArrayHasKey('Location', $headers);
        $this->assertSame('/path/to', $headers['Location']);
        $this->assertSame(302, $e->getStatusCode());
    }

}
