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

use Exception;
use Oft\Http\ResponseInterface;

class HttpException extends Exception
{
    protected $statusCode;
    protected $headers;
    protected $content;

    /**
     * Exception HTTP de haut niveau
     * 
     * @param int $statusCode Code HTTP de la réponse
     * @param array $headers Entêtes de la réponse
     * @param string $content Contenu de la réponse
     */
    public function __construct($statusCode, array $headers = array(), $content = null, $message = '', $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->content = $content;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function handleResponse(ResponseInterface $response)
    {
        $response->setStatusCode($this->statusCode)
            ->addHeaders($this->headers);
        if ($this->content !== null) {
            $response->setContent($this->content);
        }
    }
}
