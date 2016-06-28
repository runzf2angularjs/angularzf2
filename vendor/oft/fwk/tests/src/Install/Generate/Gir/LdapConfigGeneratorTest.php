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

namespace Oft\Test\Install\Generate\Gir;

use InvalidArgumentException;
use Oft\Install\Generate\Gir\LdapConfigGenerator;
use PHPUnit_Framework_TestCase;

class LdapConfigGeneratorTest extends PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        if (!defined('APP_ROOT')) {
            define('APP_ROOT', '');
        }
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGenerateException()
    {
        $generator = new LdapConfigGenerator();

        $generator->useSsl = 'not-0-or-1';

        $generator->generate();
    }

    public function testGenerateSslTrueAndFalse()
    {
        $generator = new LdapConfigGenerator();

        $generator->active = 1;
        $generator->baseDn = 'basedn';
        $generator->host = 'host';
        $generator->username = 'username';
        $generator->password = 'pwd';
        $generator->port = 386;

        $generator->useSsl = 0; // SSL FALSE
        $generator->generate();

        $generator->useSsl = 1; // SSL TRUE
        $generator->generate();

        $files = $generator->getFiles();

        $this->assertCount(2, $files);
        $this->assertInstanceOf('Oft\Install\Generate\File', $files[0]);
        $this->assertInstanceOf('Oft\Install\Generate\File', $files[1]);
    }

}
