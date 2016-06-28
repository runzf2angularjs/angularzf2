<?php

return array(
    'assets' => array(
        'path' => '/assets/v{version}/{name}/{type}/{resource}',
        'values' => array(
            'module' => 'oft',
            'controller' => 'assets',
            'action' => 'render'
        ),
        'tokens' => array(
            'version' => '[0-9]+',
            'name' => '[\w\-\.]+',
            'type' => '(js|css)',
            'resource' => '[a-z0-9]{7}[0-9]+\.(js|css)',
        )
    ),
    'assets.file' => array(
        'path' => '/assets/v{version}/{name}/{type}/{resource}',
        'values' => array(
            'module' => 'oft',
            'controller' => 'assets',
            'action' => 'render-file'
        ),
        'tokens' => array(
            'version' => '[0-9]+',
            'name' => '[\w\-\.]+',
            'type' => '[\w\-\.]+',
            'resource' => '[\w\/\.\-]+',
        )
    ),
    'user.language' => array(
        'path' => '/user/language/{language}',
        'method' => 'GET',
        'values' => array(
            'module' => 'oft',
            'controller' => 'user',
            'action' => 'language',
        ),
        'tokens' => array(
            'language' => '[a-z]{2}',
        ),
    )
);
