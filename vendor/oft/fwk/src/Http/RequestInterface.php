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

interface RequestInterface
{
    public function getFromServer($name = null, $default = null, $deep = false);
    public function getFromQuery($name = null, $default = null, $deep = false);
    public function getFromPost($name = null, $default = null, $deep = false);
    public function getFromCookies($name = null, $default = null, $deep = false);
    public function getFromHeaders($name = null, $default = null, $first = true);
    public function getBaseUrl();
    public function getBasePath();
    public function getRequestUri();
    public function getPathInfo();
    public function getHttpMethod();
    public function getQueryString();
    public function isPost();
    public function isMethod($method);
    public function isHttps();
    public function isXmlHttpRequest();
    public function getPreferredLanguage($array = null);
    public function getContent();
}
