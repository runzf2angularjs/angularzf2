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

namespace Oft\Controller;

use Exception;
use Oft\Mvc\ControllerAbstract;
use Oft\Mvc\Exception\RedirectException;

class UserController extends ControllerAbstract
{

    protected $inputFilterRules = array(
        'redirect' => array(
            'filters' => array(),
            'validators' => array(
                array(
                    'name' => 'Regex',
                    'options' => array(
                        'pattern' => '/^\/[\w0-9\/\.\-_]*(\?[\w0-9\/\.\-_]+=[\w0-9\/\.\-_ ]*){0,1}(&[\w0-9\/\.\-_]+=[\w0-9\/\.\-_ ]*)*$/iu'
                    )
                )
            )
        )
    );

    public function languageAction($language)
    {
        $this->disableRendering(true, false);

        $this->app->http->response->setCookie('lang', $language);
        
        try {
            $redirect = $this->getParam('redirect', '');
        } catch (Exception $e) {
            if ($this->app->isDebug) {
                $this->flashMessage($e->getMessage(), self::WARNING);
            }
            $redirect = '/';
        }

        $baseUrl = $this->request->getBaseUrl();
        throw new RedirectException($baseUrl . $redirect);
    }

}
