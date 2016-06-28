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

namespace Oft\Widget;

use Oft\Widget\WidgetAbstract;

class MenuWidget extends WidgetAbstract
{
    public function __invoke()
    {
        $items = $this->app->get('Menu');
        $currentRoute = $this->app->route->current;
        $username = $this->app->identity->get()->getDisplayName();
        $locale = $this->app->get('Translator')->getLocale();

        $requestUri = rawurlencode($this->app->http->request->getPathInfo());
        $queryString = $this->app->http->request->getQueryString();
        if (strlen($queryString)) {
            $requestUri .= rawurlencode('?'. $queryString) ;
        }

        return $this->render('oft/_widget/menu', array(
            'items' => $items,
            'currentRoute' => $currentRoute,
            'replacements' => array(
                '%USERNAME%' => $username,
                '%LOCALE%' => $locale,
                '%REQUEST_URI%' =>  $requestUri,
            ),
        ));
    }
}