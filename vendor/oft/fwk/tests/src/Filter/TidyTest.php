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

namespace Oft\Filter;

class TidyTest extends \PHPUnit_Framework_TestCase
{

    public function testFilter()
    {
        $html = "<html><header></header><body><div>Hello</div></body></html>";
        $tidyFilter = new \Oft\Filter\Tidy();
        $result = $tidyFilter->filter($html);

        $this->assertTrue(is_string($result));
        $this->assertRegExp("|<div>\r?\n  Hello\r?\n</div>|", $result);
    }

    public function testSetConfig()
    {
        $config = array();
        $tidyFilter = new \Oft\Filter\Tidy();
        $tidyFilter->setConfig(array());
    }

    public function testSetEncoding()
    {
        $config = array();
        $tidyFilter = new \Oft\Filter\Tidy();
        $tidyFilter->setEncoding('UTF-8');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Le paramètre "encodage" doit être une chaîne
     */
    public function testSetEncodingException()
    {
        $config = array();
        $tidyFilter = new \Oft\Filter\Tidy();
        $tidyFilter->setEncoding(78);
    }
}
