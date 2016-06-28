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

namespace Oft\Mvc\Context;

use Oft\Http\RequestInterface;
use Oft\Http\ResponseInterface;
use Oft\Http\SessionInterface;

/**
 * @property-read RequestInterface $request Http request
 * @property-read ResponseInterface $response Http response
 * @property-read SessionInterface $session Http session
 */
class HttpContext extends ContextAbstract
{

    protected $contexts = array(
        'request' => null,
        'response' => null,
        'session' => null
    );

    public function setRequest(RequestInterface $request)
    {
        $this->setContext('request', $request);

        return $this;
    }

    public function setResponse(ResponseInterface $response)
    {
        $this->setContext('response', $response);

        return $this;
    }

    public function setSession(SessionInterface $session)
    {
        $this->setContext('session', $session);

        return $this;
    }

}
