<?php

return array(
    'admin' => array(
        'name' => 'Admin',
        'position' => 90,
        'submenu' => array(
            'user' => array(
                'name' => 'Users',
                'route' => array(
                    'name' => 'users.list',
                ),
            ),
            'group' => array(
                'name' => 'Groups',
                'route' => array(
                    'name' => 'groups.list',
                ),
            ),
            'resource' => array(
                'name' => 'Resources',
                'route' => array(
                    'name' => 'resources.list',
                ),
            ),
            'acl' => array(
                'name' => 'Group access permissions',
                'route' => array(
                    'name' => 'acl.list',
                ),
            ),
        )
    ),
    'profile:is-guest' => array(
        'name' => 'Log in',
        'position' => 100,
        'align' => 'right',
        'route' => array(
            'name' => 'auth.login',
        ),
    ),
    'profile:is-not-guest' => array(
        'name' => '%USERNAME%',
        'position' => 100,
        'align' => 'right',
        'submenu' => array(
            'profile' => array(
                'name' => 'Profile',
                'route' => array(
                    'name' => 'user.profile',
                ),
            ),
            'password' => array(
                'name' => 'Change password',
                'route' => array(
                    'name' => 'user.change',
                ),
            ),
            'disconnect' => array(
                'name' => 'Log out',
                'route' => array(
                    'name' => 'auth.logout',
                ),
            )
        ),
    ),
);
