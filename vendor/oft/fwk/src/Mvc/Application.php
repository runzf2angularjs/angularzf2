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

namespace Oft\Mvc;

use Doctrine\DBAL\Portability\Connection;
use Oft\Http\ResponseInterface;
use Oft\Module\ModuleManager;
use Oft\Mvc\Context\HttpContext;
use Oft\Mvc\Context\IdentityContext;
use Oft\Mvc\Context\RenderOptionsContext;
use Oft\Mvc\Context\RouteContext;
use Oft\Mvc\MiddlewareAbstract;
use Oft\Service\ServiceManager;
use Oft\Util\Arrays;
use Oft\Util\Functions;
use Oft\View\View;

/**
 * @property-read RouteContext $route Contexte de route
 * @property-read HttpContext $http Contexte HTTP
 * @property-read IdentityContext $identity Contexte d'identité
 * @property-read RenderOptionsContext $renderOptions Options de rendu
 * @property-read Connection $db Connexion SGBD
 * @property-read View $view Objet de vue
 * @property-read array $config Configuration
 * @property-read bool $isDebug En mode debug
 * @property-read bool $isCli En mode CLI
 */
class Application extends ServiceManager
{

    /**
     * Configuration de l'application
     *
     * @var array
     */
    protected $config = array();

    /**
     * Gestionnaire de modules
     *
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * Middlewares
     *
     * @var array
     */
    protected $middlewares = array();

    /** @var bool */
    protected $inRun = false;

    /** @var bool */
    protected $configMerged = false;

    /** @var bool */
    protected $isDebug = false;

    /** @var bool */
    protected $isCli = false;

    /** 
     * Contexte de route.
     *
     * Route courante, précédente, par défaut, non trouvée et d'erreur
     * 
     * @var RouteContext
     */
    protected $route;

    /** 
     * Contexte HTTP.
     *
     * Requête, réponse et session HTTP
     *
     * @var HttpContext
     */
    protected $http;

    /**
     * Identité de l'utilisateur
     * 
     * @var IdentityContext
     */
    protected $identity;

    /** 
     * Base de donnée principale.
     * 
     * @var Connection
     */
    protected $db;

    /**
     * Objet de rendu de vue.
     *
     * @var View
     */
    protected $view;

    /** 
     * Options de rendu.
     * 
     * @var RenderOptionsContext
     */
    protected $renderOptions;

    /**
     * Constructeur de l'application
     *
     * @param array $mainConfig Configuration initiale de l'application
     * @param ModuleManager $moduleManager
     */
    public function __construct(array $mainConfig = array(), ModuleManager $moduleManager = null)
    {
        if ($moduleManager === null) {
            $this->moduleManager = new ModuleManager();
        } else {
            $this->moduleManager = $moduleManager;
        }

        $this->setService('ModuleManager', $this->moduleManager);

        $this->setMainConfig($mainConfig);
    }

    /**
     * Définit la configuration de l'application
     *
     * @param array $mainConfig Configuration de l'application
     */
    protected function setMainConfig(array $mainConfig)
    {
        foreach ($mainConfig as $key => $value) {
            switch ($key) {
                case 'debug':
                    $this->isDebug = (bool) $value;
                    break;
                case 'cli':
                    $this->isCli = (bool) $value;
                    break;
                case 'defaultModule':
                    $this->moduleManager->addModule($value, true);
                    break;
                case 'modules':
                    $this->moduleManager->addModules($value);
                    break;
                case 'middlewares':
                    $this->setMiddlewares($value);
                    break;
                default:
                    $this->config[$key] = $value;
                    break;
            }
        }

        return $this;
    }

    /**
     * Définit les middlewares
     *
     * @param array $middlewares
     * @return self
     */
    protected function setMiddlewares(array $middlewares)
    {
        $this->middlewares = array();
        foreach ($middlewares as $middleware) {
            $this->addMiddleware($middleware);
        }

        return $this;
    }

    /**
     * Ajoute un middleware
     *
     * @param string|MiddlewareAbstract $middleware
     * @return self
     */
    protected function addMiddleware($middleware)
    {
        if (is_string($middleware)) {
            $middleware = new $middleware;
        }

        $index = count($this->middlewares);
        if ($index > 0) {
            $this->middlewares[$index - 1]->setNextMiddleware($middleware);
        }

        $this->middlewares[$index] = $middleware;

        return $this;
    }

    protected function doMergeConfig()
    {
        // Get config from modules
        $config = $this->moduleManager->getModulesConfig($this->isCli);

        // Get env specific config (APP_ENV support)
        if (isset($config['env'])) {
            if (isset($config['env']['default'])) {
                $config = Arrays::mergeConfig($config, $config['env']['default']);
            }
            
            if (defined('APP_ENV') && isset($config['env'][APP_ENV])) {
                $config = Arrays::mergeConfig($config, $config['env'][APP_ENV]);
            }

            unset($config['env']);
        }

        // The main config comes in latest position
        $this->config = Arrays::mergeConfig($config, $this->config);
    }

    protected function mergeConfig()
    {
        if ($this->isDebug || $this->isCli) {
            $this->doMergeConfig();
            return;
        }

        try {
            // Get from cache
            $this->config = include CACHE_DIR . '/config.php';
        } catch (\Exception $e) {
            $this->doMergeConfig();

            file_put_contents(CACHE_DIR . '/config.php', '<?php return ' . var_export($this->config, true) . ';');
        }
    }

    /**
     * Proxy vers les variables protégées ainsi que vers certains services
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        // protected attributes
        if (isset($this->$name)) { // isset **IS** important
            return $this->$name;
        }

        // Services shorthand
        if (in_array($name, array('route', 'http', 'identity', 'db', 'view', 'renderOptions'))) {
            $this->$name = $this->get(ucfirst($name));

            return $this->$name;
        }

        throw new \RuntimeException("Unable to get that property ($name)");
    }

    protected function initServiceManager()
    {
        $serviceManagerConfig = $this->config['services'];

        foreach ($serviceManagerConfig as $name => $class) {
            $this->addServiceDefinition($name, $class);
        }
    }

    /**
     * Phase d'initialisation
     */
    public function init()
    {
        // Merge configuration
        $this->mergeConfig();

        // Init functions
        Functions::setApp($this);

        // Init ServiceManager
        $this->initServiceManager();

        // Init logger
        $this->get('Log');
        
        // Init translator to validators
        $this->get('Translator');

        // Init modules
        $this->moduleManager->init($this);
    }

    /**
     * Lancement de l'application
     *
     * @return ResponseInterface
     */
    public function run()
    {
        if ($this->inRun) {
            throw new \RuntimeException("Application is already in run state");
        }
        $this->inRun = true;

        // Init app
        $this->init();

        try {
            $this->middlewares[0]->call($this);
        } catch (Exception\HttpException $e) {
            $e->handleResponse($this->http->response);
        }

        $this->inRun = false;

        return $this->http->response;
    }

}
