<?php

return array(
    'assets' => array(
        'filters' => array(
            'CssMinFilter' => array(
                'class' => 'Assetic\Filter\CssMinFilter',
                'args' => array(),
            ),
        ),
        'defaults' => array(
            '@bootstrap',
            '@oft',
        ),
        'collections' => array(
            'bootstrap' => array(
                'module' => 'oft-ihm',
                'assets' => array(
                    '@html5shiv',
                    '@jquery',
                    array(
                        'type' => 'js',
                        'files' => array(
                            'bootstrap/js/bootstrap.min.js',
                        ),
                    ),
                    array(
                        'type' => 'css',
                        'files' => array(
                            'bootstrap/css/bootstrap.min.css',
//                        'bootstrap/css/bootstrap-theme.min.css',
                        ),
                    ),
                ),
            ),
            'html5shiv' => array(
                'module' => 'oft-ihm',
                'assets' => array(
                    array(
                        'type' => 'js',
                        'tag' => array(
                            'type' => 'text/javascript',
                            'attrs' => array('conditional' => 'lt IE 9'),
                        ),
                        'files' => array(
                            'html5/html5shiv.min.js',
                            'html5/respond.min.js',
                        ),
                    ),
                ),
            ),
            'jquery' => array(
                'module' => 'oft-ihm',
                'assets' => array(
                    array(
                        'type' => 'js',
                        'files' => array(
                            'jquery/jquery.min.js',
                        ),
                    ),
                ),
            ),
            'oft' => array(
                'module' => 'oft-ihm',
                'assets' => array(
                    array(
                        'type' => 'css',
                        'filters' => array(
                            '?CssMinFilter'
                        ),
                        'files' => array(
                            'oft/css/style.css',
                        ),
                    ),
                ),
            ),
            'typeahead' => array(
                'module' => 'oft-ihm',
                'assets' => array(
                    '@jquery',
                    array(
                        'type' => 'js',
                        'files' => array(
                            'typeahead/js/bootstrap3-typeahead.min.js',
                        ),
                    ),
                ),
            ),
            'autocomplete' => array(
                'module' => 'oft-ihm',
                'assets' => array(
                    '@jquery',
                    '@typeahead',
                    array(
                        'type' => 'js',
                        'files' => array(
                            'autocomplete/js/autocomplete.js',
                        ),
                    ),
                ),
            ),
        ),
    ),
);
