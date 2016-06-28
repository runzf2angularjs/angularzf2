<?php

return array(
    // Maximum number of forward
    'maxForward' => 10,

    'routeContext' => array(
        // Default route for '/'
        'default' => array(
            'module' => '@default',
            'controller' => 'index',
            'action' => 'index'
        ),

        // 404 route
        'notFound' => array(
            'module' => 'oft',
            'controller' => 'error',
            'action' => 'not-found'
        ),

        // Error route
        'error' => array(
            'module' => 'oft',
            'controller' => 'error',
            'action' => 'error'
        ),
    ),
    
    // Acl
    'acl' => array(
        'whitelist' => array(
            'mvc.oft',
        ),
    ),
    
    // Authentification
    'auth' => array(
        'expiration' => array(
            'seconds' => 3600, // 1 heure
        ),
    ),

    // Composant d'échappement
    'escaper' => array(
        'encoding' => 'utf-8',
    ),

    // Session key parameters
    'session' => array(
        'name' => 'SID',
        'auto_start' => false,
        'cookie_lifetime' => 0,
        'cookie_path' => '/',
        'cookie_domain' => '',
        'cookie_httponly' => true, // fix #618
        'use_strict_mode' => true, // 5.5 only
        'use_cookie' => true,
        'use_only_cookies' => true,
        'cache_limiter' => 'nocache',
        'use_trans_sid' => false
    ),
    
    // Langue dans le menu
    'menu-bar' => array(
        'lang' => array(
            'position' => 999,
            'align' => 'right'
        ),
    ),
    
    //Date
    'date' => array(
        'timezone' => null,
    ),

    // Layout properties
    'layout' => array(
        'default' => 'default',
        'path' => 'oft/layout',
    ),
    
    // Log
    'log' => include __DIR__ . '/config.log.php',

    // Services
    'services' => include __DIR__ . '/config.services.php',

    // View helpers
    'view' => include __DIR__ . '/config.view.php',

    // Widgets
    'widgets' => include __DIR__ . '/config.widgets.php',

    // Routes
    'routes' => include __DIR__ . '/config.routes.php',
    
    // Fichiers statiques
    'assets' => include __DIR__ . '/config.assets.php',
    
    // Groupe Identity Referencial
    'gir' => include __DIR__ . '/config.gir.php',
        
    // Traductions
    'translator' => include __DIR__ . '/config.translator.php',

    // Db
    'db' => include __DIR__ . '/config.db.php',

    // Dbal
    'dbal' => include __DIR__ . '/config.dbal.php',

    // Liens du footer
    'footer-links' => include __DIR__ . '/config.footer-links.php',
);
