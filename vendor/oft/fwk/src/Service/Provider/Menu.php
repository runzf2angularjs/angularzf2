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

namespace Oft\Service\Provider;

use Oft\Menu\Items;
use Oft\Service\FactoryInterface;
use Oft\Service\ServiceLocatorInterface;

class Menu implements FactoryInterface
{

    /** @var \Oft\Acl\Acl */
    protected $acl;

    /** @var \Aura\Router\Router */
    protected $router;

    /** @var string */
    protected $baseUrl;

    /** @var \Oft\Auth\Identity */
    protected $identity;

    /** @var \Zend\I18n\Translator\Translator */
    protected $translator;

    /** @var array */
    protected $availableLanguages;

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return Items
     */
    public function create(ServiceLocatorInterface $serviceLocator)
    {
        $this->acl = $serviceLocator->get('Acl');
        $this->router = $serviceLocator->get('Router');
        $this->baseUrl = $serviceLocator->http->request->getBaseUrl();
        $this->identity = $serviceLocator->identity->get();
        $this->translator = $serviceLocator->get('Translator');

        $translateConfig = $serviceLocator->config['translator'];
        $availableLanguages = array($translateConfig['default']['locale']);
        if (!empty($translateConfig['availableLanguages'])) {
            $availableLanguages = array_unique(array_merge($availableLanguages, $translateConfig['availableLanguages']));
        }
        $this->availableLanguages = $availableLanguages;

        $menuItems = $serviceLocator->config['menu-bar'];
        
        if (isset($menuItems['lang']) && is_array($menuItems['lang'])) {
            $langMenu = $this->getLangMenu();
            if (is_array($langMenu)) {
                $langMenu['position'] = $menuItems['lang']['position'];
                $langMenu['align'] = $menuItems['lang']['align'];
                $menuItems['lang'] = $langMenu;
            } else {
                unset($menuItems['lang']);
            }
        }

        $items = $this->getSubMenu($menuItems);

        return $items;
    }

    public function checkItemName($name)
    {
        if (strpos($name, ':is-guest') !== false && !$this->identity->isGuest()) {
            return false;
        }

        if (strpos($name, ':is-not-guest') !== false && $this->identity->isGuest()) {
            return false;
        }

        if (strpos($name, ':is-admin') !== false && !$this->identity->isAdmin()) {
            return false;
        }

        return true;
    }

    public function getItem(array $item, $name)
    {
        if (!$this->checkItemName($name)) {
            return false;
        }

        if (isset($item['route'])) {
            if (!$this->acl->isMvcAllowed($item['route'], $this->identity)) {
                return false;
            }

            // Add module, controller, action info to route
            $item['route'] = $this->acl->getRouteParams($item['route']);

            // Génération de l'url
            $routeName = $this->acl->getRouteName($item['route']);
            $item['url'] = $this->baseUrl . $this->router->generate($routeName, $item['route']);
        }

        if (isset($item['submenu'])) {
            $subMenuItems = $this->getSubMenu($item['submenu']);
            if (count($subMenuItems)) {
                $item['submenu'] = $subMenuItems;
            } else {
                unset($item['submenu']);
            }

            // Supression de l'entrée si elle n'a pas de lien et que
            // les sous menus ne sont pas accessibles
            if (!isset($item['submenu']) && !isset($item['url'])) {
                return false;
            }
        }

        return $item;
    }

    public function getSubMenu(array $items)
    {
        $subMenuItems = new Items();
        foreach ($items as $name => $item) {
            if (empty($item)) {
                continue;
            }
            
            $newitem = $this->getItem($item, $name);
            if ($newitem) {
                $subMenuItems->addItem($newitem, $name);
            }
        }
        $subMenuItems->uasort(array($this, 'itemCompareCallback'));

        return $subMenuItems;
    }

    public function itemCompareCallback($item1, $item2)
    {
        // Identiques si position non définie
        $pos1 = isset($item1['position']) ? $item1['position'] : 0;
        $pos2 = isset($item2['position']) ? $item2['position'] : 0;

        if ($pos1 < $pos2) {
            return -1;
        } elseif ($pos1 == $pos2) {
            return 0;
        } else {
            return 1;
        }
    }

    public function getLangMenu()
    {
        if (count($this->availableLanguages) <= 1) {
            return false;
        }

        $item = array(
            'name' => '<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" class="flag flag-%LOCALE%" alt="%LOCALE%" /> ',
            'type' => 'image',
        );

        foreach ($this->availableLanguages as $language) {
            $item['submenu'][] = array(
                'name' => '<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" class="flag flag-' . $language . '" alt="' . $language . '" /> ' . $this->translator->translate($language),
                'type' => 'image',
                'url' => $this->baseUrl . $this->router->generate('user.language', array(
                        'language' => $language
                    )) . '?redirect=%REQUEST_URI%',
            );
        }

        return $item;
    }

}
