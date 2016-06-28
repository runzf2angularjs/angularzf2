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

namespace Oft\Test\Mock;

use Oft\Mvc\Context\HttpContext as OftHttpContext;

class HttpContext extends OftHttpContext
{
    public function __construct()
    {
        $contexts = array(
            'request' => \Mockery::mock('Oft\Http\RequestInterface'),
            'response' => \Mockery::mock('Oft\Http\ResponseInterface'),
            'session' => \Mockery::mock('Oft\Http\SessionInterface'),
        );

        parent::__construct($contexts);
    }
}