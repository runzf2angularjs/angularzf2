<?php

return array(
    'acl' => array(
        'whitelist' => array(
            'mvc.oft-admin.auth',
            'mvc.oft-admin.user',
        )
    ),
    'auth' => array(
        'logout-url' => array(
            // Contrôleur "index", action "index" du module par défaut
        ),
    ),
    'services' => include __DIR__ . '/config.services.php',
    'routes' => include __DIR__ . '/config.routes.php',
    'menu-bar' => include __DIR__ . '/config.menubar.php',
    'assets' => include __DIR__ . '/config.assets.php',
    'gir' => include __DIR__ . '/config.gir.php',
);
