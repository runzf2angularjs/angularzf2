<?php

return array(
    'Debug' => 'Oft\Debug\Disabled',
    'ControllerFactory' => 'Oft\Service\Provider\ControllerFactory',
    'Db' => 'Oft\Service\Provider\Db',
    'Router' => 'Oft\Service\Provider\Router',
    'View' => 'Oft\Service\Provider\View',
    'ViewHelperPlugins' => 'Oft\Service\Provider\ViewHelperPlugins',
    'Acl' => 'Oft\Acl\AclFactory',
    'AclStore' => 'Oft\Acl\Adapter\Db',
    'Auth' => 'Oft\Auth\LoginPasswordAuth',
    'IdentityStore' => 'Oft\Auth\IdentityStore\DbTable',
    'AssetManager' => 'Oft\Service\Provider\AssetManager',
    'Http' => 'Oft\Service\Provider\HttpContext',
    'Identity' => 'Oft\Service\Provider\IdentityContext',
    'Route' => 'Oft\Service\Provider\RouteContext',
    'RenderOptions' => 'Oft\Service\Provider\RenderOptionsContext',
    'Log' => 'Oft\Service\Provider\Log',
    'Widget' => 'Oft\Widget\WidgetFactory',
    'Gir' => 'Oft\Gir\Ldap',
    'Translator' => 'Oft\Service\Provider\Translator',
    'DateFormatter' => 'Oft\Date\DateFormatter',
    'Menu' => 'Oft\Service\Provider\Menu'
);
