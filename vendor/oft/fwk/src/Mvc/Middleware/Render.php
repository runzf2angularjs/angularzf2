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

use Exception;
use Oft\Mvc\Application;
use Oft\Mvc\MiddlewareAbstract;

class Render extends MiddlewareAbstract
{

    /**
     * Implémentation du middleware
     *
     * @param Application $app Conteneur d'application
     */
    public function call(Application $app)
    {
        // Call next capturing echo'ed content
        $this->next->call($app);

        // Render view model
        if ($app->renderOptions->renderView) {

            // Set template
            $template = $app->renderOptions->viewTemplate;
            if (!strlen($template)) {
                $route = $app->route->current;
                $template = $route['module']
                    . '/' . $route['controller']
                    . '/' . $route['action'];
            }

            // Render content
            $content = $app->view->render(
                $template,
                $app->renderOptions->viewModel->getArrayCopy()
            );

            // Append action content at the end (ZF1-like)
            $app->http->response->prependContent($content);
        }
    }

}
