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

namespace Oft\Mvc\Exception;

class RedirectException extends HttpException
{

    /**
     * Initialisation
     *
     * @param string $url URL ciblée par la redirection
     * @param type $message Message
     * @param type $code Code HTTP
     * @param type $previous Exception précédente
     * @return self
     */
    public function __construct($url, $httpCode = 302, $content = '', $message = '', $code = 0, $previous = null)
    {
        $headers = array(
            'Location' => $url,
        );
        parent::__construct($httpCode, $headers, $content, $message, $code, $previous);
    }

}
