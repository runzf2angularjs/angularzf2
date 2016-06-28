<?php

return array(
    'services' => array(
        'Debug' => 'Oft\Debug\Service\Debug',
    ),
    'assets' => array(
        'options' => array(
            // Désactivation du cache et des filtres préfixés "?"
            'debug' => true,
        ),
        'defaults' => array(
            '@debug-bar',
        ),
        'collections' => array(
            'font-awesome' => array(
                'module' => 'debug',
                'assets' => array(
                    array(
                        'type' => 'css',
                        'files' => array(
                            'font-awesome/css/font-awesome.min.css'
                        )
                    )
                )
            ),
            'debug-bar' => array(
                'module' => 'debug',
                'assets' => array(
                    '@jquery',
                    '@font-awesome',
                    array(
                        'type' => 'css',
                        'files' => array(
                            'debug-bar/debugbar.css',
                            'debug-bar/widgets.css',
                            'debug-bar/openhandler.css',
                            'debug-bar/widgets/sqlqueries/widget.css',
                        )
                    ),
                    array(
                        'type' => 'js',
                        'files' => array(
                            'debug-bar/debugbar.js',
                            'debug-bar/widgets.js',
                            'debug-bar/openhandler.js',
                            'debug-bar/widgets/sqlqueries/widget.js',
                        )
                    ),
                ),
            )
        )
    ),
    'view' => array(
        'helpers' => array(
            'debugBar' => 'Oft\Debug\View\Helper\DebugBar',
        ),
    )
);
