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

class TestController
{
    public $application;
    public $httpRequest;
    public $httpResponse;
    public $serviceLocator;
    public $viewModel;
    public $params;
    public $init = false;
    
    public function setApplication($param)
    {
        $this->application = $param;
    }
    
    public function setHttpRequest($param)
    {
        $this->httpRequest = $param;
    }
    
    public function setHttpResponse($param)
    {
        $this->httpResponse = $param;
    }
    
    public function setServiceLocator($param)
    {
        $this->serviceLocator = $param;
    }
    
    public function setViewModel($param)
    {
        $this->viewModel = $param;
    }
    
    public function setParams($param)
    {
        $this->params = $param;
    }
    
    public function init()
    {
        $this->init = true;
    }
    
    public function testAction()
    {
        
    }
}