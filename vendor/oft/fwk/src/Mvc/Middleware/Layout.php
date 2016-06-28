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

namespace Oft\Mvc\Middleware;

use Oft\Mvc\Application;
use Oft\Mvc\MiddlewareAbstract;

class Layout extends MiddlewareAbstract
{

    /**
     * Implémentation du middleware
     *
     * @param Application $app Conteneur d'application
     */
    public function call(Application $app)
    {
        // Call next
        $this->next->call($app);

        // If disabled return the content
        if (! $app->renderOptions->renderLayout) {
            return;
        }

        // Get layout Params
        $layoutTemplate = $app->renderOptions->layoutTemplatePath
            . '/' . $app->renderOptions->layoutTemplateName;

        // Render & return
        $content = $app->view->render($layoutTemplate, array(
            'content' => $app->http->response->getContent()
        ));

        $app->http->response->setContent($content);
    }

}
