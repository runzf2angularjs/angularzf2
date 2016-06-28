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

namespace Oft\View;

use Oft\Mvc\Application;
use Zend\View\HelperPluginManager;
use Zend\View\Model\ModelInterface;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\RendererInterface;
use Zend\View\Resolver\ResolverInterface;

/**
 * @property-read Application $app
 * @method \Oft\View\Helper\Assets assets()
 * @method string basePath($url = null)
 * @method string baseUrl($url = null)
 * @method \Oft\View\Helper\Breadcrumb breadcrumb($text = null, $link = null)
 * @method \Oft\View\Helper\Datagrid datagrid($idColumn, $iterator, $columnsOptions, array $options = array())
 * @method string dateFormatter($date, $dateFormatOut = 'short', $timeFormatOut = 'medium', $dateFormatIn = 'sql', $timeFormatIn = 'sql')
 * @method mixed debugBar()
 * @method string flashMessenger($text = null, $type = self::INFO)
 * @method \Oft\View\Helper\Footer footer()
 * @method \Oft\Auth\Identity identity()
 * @method \Oft\View\Helper\SmartElement smartElement(\Zend\Form\Element $element, array $options = array())
 * @method \Oft\View\Helper\SmartForm smartForm(\Zend\Form\Form $form, array $options = array())
 * @method string smartUrl($action = null, $controller = null, $module = null, array $params = array(), $routeName = null)
 * @method string smartUrlFromRoute($route = null, array $params = array())
 * @method string title($text = null)
 * @method \Oft\View\Helper\Widget widget($name, array $context = array())
 * 
 * @method \Zend\View\Helper\Cycle cycle(array $data = array(), $name = \Zend\View\Helper\Cycle::DEFAULT_NAME)
 * @method \Zend\View\Helper\DeclareVars declareVars()
 * @method \Zend\View\Helper\Doctype doctype($doctype = null)
 * @method mixed escapeCss($value, $recurse = \Zend\View\Helper\Escaper\AbstractHelper::RECURSE_NONE)
 * @method mixed escapeHtml($value, $recurse = \Zend\View\Helper\Escaper\AbstractHelper::RECURSE_NONE)
 * @method mixed escapeHtmlAttr($value, $recurse = \Zend\View\Helper\Escaper\AbstractHelper::RECURSE_NONE)
 * @method mixed escapeJs($value, $recurse = \Zend\View\Helper\Escaper\AbstractHelper::RECURSE_NONE)
 * @method mixed escapeUrl($value, $recurse = \Zend\View\Helper\Escaper\AbstractHelper::RECURSE_NONE)
 * @method \Zend\View\Helper\Gravatar gravatar($email = "", $options = array(), $attribs = array())
 * @method \Zend\View\Helper\HeadLink headLink(array $attributes = null, $placement = \Zend\View\Helper\Placeholder\Container\AbstractContainer::APPEND)
 * @method \Zend\View\Helper\HeadMeta headMeta($content = null, $keyValue = null, $keyType = 'name', $modifiers = array(), $placement = \Zend\View\Helper\Placeholder\Container\AbstractContainer::APPEND)
 * @method \Zend\View\Helper\HeadScript headScript($mode = \Zend\View\Helper\HeadScript::FILE, $spec = null, $placement = 'APPEND', array $attrs = array(), $type = 'text/javascript')
 * @method \Zend\View\Helper\HeadStyle headStyle($content = null, $placement = 'APPEND', $attributes = array())
 * @method \Zend\View\Helper\HeadTitle headTitle($title = null, $setType = null)
 * @method string htmlFlash($data, array $attribs = array(), array $params = array(), $content = null)
 * @method string htmlList(array $items, $ordered = false, $attribs = false, $escape = true)
 * @method string htmlObject($data = null, $type = null, array $attribs = array(), array $params = array(), $content = null)
 * @method string htmlPage($data, array $attribs = array(), array $params = array(), $content = null)
 * @method string htmlQuicktime($data, array $attribs = array(), array $params = array(), $content = null)
 * @method \Zend\View\Helper\InlineScript inlineScript($mode = \Zend\View\Helper\HeadScript::FILE, $spec = null, $placement = 'APPEND', array $attrs = array(), $type = 'text/javascript')
 * @method string|void json($data, array $jsonOptions = array())
 * @method \Zend\View\Helper\Layout layout($template = null)
 * @method \Zend\View\Helper\Navigation navigation($container = null)
 * @method string paginationControl(\Zend\Paginator\Paginator $paginator = null, $scrollingStyle = null, $partial = null, $params = null)
 * @method string|\Zend\View\Helper\Partial partial($name = null, $values = null)
 * @method string partialLoop($name = null, $values = null)
 * @method \Zend\View\Helper\Placeholder\Container\AbstractContainer placeHolder($name = null)
 * @method string renderChildModel($child)
 * @method void renderToPlaceholder($script, $placeholder)
 * @method string serverUrl($requestUri = null)
 * @method string url($name = null, array $params = array(), $options = array(), $reuseMatchedParams = false)
 * @method \Zend\View\Helper\ViewModel viewModel()
 * @method \Zend\View\Helper\Navigation\Breadcrumbs breadCrumbs($container = null)
 * @method \Zend\View\Helper\Navigation\Links links($container = null)
 * @method \Zend\View\Helper\Navigation\Menu menu($container = null)
 * @method \Zend\View\Helper\Navigation\Sitemap sitemap($container = null)
 * 
 * @method \Zend\I18n\View\Helper\CurrencyFormat currencyformat($number, $currencyCode = null, $showDecimals = null, $locale = null, $pattern = null)
 * @method \Zend\I18n\View\Helper\DateFormat dateformat($date, $dateType = IntlDateFormatter::NONE, $timeType = IntlDateFormatter::NONE, $locale = null, $pattern = null)
 * @method \Zend\I18n\View\Helper\NumberFormat numberformat($number, $formatStyle = null, $formatType = null, $locale = null, $decimals = null)
 * @method \Zend\I18n\View\Helper\Plural plural($strings, $number)
 * @method \Zend\I18n\View\Helper\Translate translate($message, $textDomain = null, $locale = null)
 * @method \Zend\I18n\View\Helper\TranslatePlural translateplural($singular, $plural, $number, $textDomain = null, $locale = null)
 */
class View implements RendererInterface
{
    /** @var Application */
    protected $app;

    /** @var string */
    protected $baseUrl;

    /** @var ResolverInterface */
    protected $resolver;

    /** @var HelperPluginManager */
    protected $helperPluginManager;

    /** @var array */
    protected $helperPluginCache = array();

    /** @var array */
    protected $__vars = array();

    /** @var int */
    protected $__varsIndex = -1;

    public function setApplication(Application $app)
    {
        $this->app = $app;

        return $this;
    }

    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = \rtrim($baseUrl, '/');

        return $this;
    }

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    public function setHelperPluginManager(HelperPluginManager $viewPlugins)
    {
        $this->helperPluginManager = $viewPlugins;
        $this->helperPluginCache = array();

        return $this;
    }

    public function getHelperPluginManager()
    {
        return $this->helperPluginManager;
    }

    public function getEngine()
    {
        return $this;
    }

    /**
     * Définit le composant de résolution de la vue
     *
     * @param ResolverInterface
     * @return void
     */
    public function setResolver(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;

        return $this;
    }

    public function getResolver()
    {
        return $this->resolver;
    }

    /**
     * Effectue le rendu de la vue
     *
     * @param ModelInterface $viewModelParam
     * @param array $valuesParam
     * @return string
     */
    public function render($viewModelParam, $valuesParam = null)
    {
        if (is_string($viewModelParam)) {
            $template = $viewModelParam;
            $viewModel = new ViewModel();
        } elseif ($viewModelParam instanceof ModelInterface) {
            $template = $viewModelParam->getTemplate();
            $viewModel = $viewModelParam;
        } else {
            throw new \RuntimeException("First param of render must be a template or a Zend\View\Model\ModelInterface");
        }

        if (is_array($valuesParam)) {
            $viewModel->setVariables($valuesParam);
        } elseif (! is_null($valuesParam)) {
            throw new \RuntimeException("Second param of render must be an array or null");
        }

        $file = $this->resolver->resolve($template);

        $vars = (array)$viewModel->getVariables();
        $this->__vars[++$this->__varsIndex] = $vars;

        try {
            $result = $this->doRender($file, $vars);
            array_pop($this->__vars);
            $this->__varsIndex--;
            return $result;
        } catch (\Exception $e) {
            // Interception d'éventuelles exceptions pour supprimer les variables de la pile
            array_pop($this->__vars);
            $this->__varsIndex--;
            throw $e;
        }
    }

    protected function doRender($__viewFilename, array $__vars)
    {
        extract($__vars);

        ob_start();
        include $__viewFilename;
        return ob_get_clean();
    }

    public function __get($name)
    {
        switch ($name) {
            case 'app':
                return $this->app;
        }

        if (!$this->__isset($name)) {
            return null;
        }

        return $this->__vars[$this->__varsIndex][$name];
    }

    public function __isset($name)
    {
        return isset($this->__vars[$this->__varsIndex][$name]);
    }

    public function plugin($name, array $options = null)
    {
        if (!isset($this->helperPluginCache[$name])) {
            $this->helperPluginCache[$name] = $this->helperPluginManager->get($name, $options);
        }

        $this->helperPluginCache[$name]->setView($this);

        return $this->helperPluginCache[$name];
    }

    public function __call($name, $argv)
    {
        $plugin = $this->plugin($name);

        return call_user_func_array($plugin, $argv);
    }
}
