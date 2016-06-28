<?php

return array(
    'collections' => array(
        'oft-admin' => array(
            'module' => 'oft-admin',
            'assets' => array(
                '@bootstrap',
                '@typeahead',
                array(
                    'type' => 'js',
                    'files' => array(
                        'oft-admin/js/users.js',
                    ),
                ),
            ),
        ),
    ),
);
