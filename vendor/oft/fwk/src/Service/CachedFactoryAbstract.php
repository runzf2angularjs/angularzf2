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

namespace Oft\Service;

use Exception;
use Oft\Mvc\Application;

abstract class CachedFactoryAbstract implements FactoryInterface
{

    /** @var bool */
    protected $isDebug;

    /** @var string */
    protected $cacheFileName;

    protected function getCacheData()
    {
        if ($this->isDebug) {
            return false;
        }

        $content = @file_get_contents($this->cacheFileName);
        if ($content === false) {
            // Pas d'erreur si le fichier n'existe pas
            return false;
        }

        try {
            $result = unserialize($content);
        } catch (\Exception $e) {
            // Si le problème est dans unserialize, on trace l'erreur et on supprime le fichier
            oft_exception($e);
            unlink($this->cacheFileName);

            return false;
        }

        return $result;
    }

    protected function putCacheData($data)
    {
        if ($this->isDebug) {
            return false;
        }

        try {
            file_put_contents($this->cacheFileName, serialize($data));
        } catch (Exception $e) {
            oft_exception($e);

            return false;
        }

        return true;
    }

    protected function initFromApp(Application $app)
    {
        $this->isDebug = $app->isDebug;
        $this->cacheFileName = $app->config['cache']['dir'] . '/'
            . str_replace('\\', '-', get_class($this))
            . '.ser';
    }

    final public function create(ServiceLocatorInterface $app)
    {
        $this->initFromApp($app);
        
        $result = $this->getCacheData();
        if ($result !== false) {
            return $result;
        }

        $result = $this->doCreate($app);

        $this->putCacheData($result);

        return $result;
    }

    abstract public function doCreate(ServiceLocatorInterface $app);
}
