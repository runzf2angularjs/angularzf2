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

namespace Oft\Http;

interface ResponseInterface
{
    public function setStatusCode($code, $phrase = null);
    public function getStatusCode();
    public function setContentType($type, $charset = 'UTF-8');
    public function getContentType();
    public function setCookie($name, $value = null, $expire = 0, $path = '/', $domain = null, $secure = false, $httpOnly = true);
    public function deleteCookie($name, $path = '/', $domain = null);
    public function setContent($content);
    public function getContent();
    public function addContent($content);
    public function prependContent($content);
    public function setHeader($key, $values, $replace = true);
    public function addHeaders(array $headers);
    public function send();
}
