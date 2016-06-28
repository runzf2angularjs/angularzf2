<?php

return array(
    'auth.login' => array(
        'path' => '/auth/login',
        'method' => 'GET|POST',
        'values' => array(
            'module' => 'oft-admin',
            'controller' => 'auth',
            'action' => 'login'
        )
    ),
    'auth.logout' => array(
        'path' => '/auth/logout',
        'method' => 'GET',
        'values' => array(
            'module' => 'oft-admin',
            'controller' => 'auth',
            'action' => 'logout'
        )
    ),
    'users.list' => array(
        'path' => '/admin/users',
        'method' => array(
            'GET',
            'POST'
        ),
        'values' => array(
            'module' => 'oft-admin',
            'controller' => 'users',
            'action' => 'index'
        )
    ),
    'users.edit' => array(
        'path' => '/admin/users/edit/{id}',
        'method' => array(
            'GET',
            'POST'
        ),
        'values' => array(
            'module' => 'oft-admin',
            'controller' => 'users',
            'action' => 'edit'
        ),
        'tokens' => array(
            'id' => '\d+'
        )
    ),
    'users.view' => array(
        'path' => '/admin/users/view/{id}',
        'method' => array(
            'GET',
            'POST'
        ),
        'values' => array(
            'module' => 'oft-admin',
            'controller' => 'users',
            'action' => 'view'
        ),
        'tokens' => array(
            'id' => '\d+'
        )
    ),
    'users.create' => array(
        'path' => '/admin/users/create',
        'method' => array(
            'GET',
            'POST'
        ),
        'values' => array(
            'module' => 'oft-admin',
            'controller' => 'users',
            'action' => 'create'
        )
    ),
    'users.delete' => array(
        'path' => '/admin/users/delete/{id}',
        'values' => array(
            'module' => 'oft-admin',
            'controller' => 'users',
            'action' => 'delete'
        ),
        'tokens' => array(
            'id' => '\d+'
        )
    ),
    'user.profile' => array(
        'path' => '/user/profile',
        'values' => array(
            'module' => 'oft-admin',
            'controller' => 'user',
            'action' => 'profile'
        )
    ),
    'user.change' => array(
        'path' => '/user/change',
        'values' => array(
            'module' => 'oft-admin',
            'controller' => 'user',
            'action' => 'change'
        )
    ),
    'user.forgot' => array(
        'path' => '/user/forgot',
        'method' => 'GET|POST',
        'values' => array(
            'module' => 'oft-admin',
            'controller' => 'user',
            'action' => 'forgot'
        )
    ),
    'user.reset' => array(
        'path' => '/user/reset/username/{username}/token/{token}',
        'method' => 'GET|POST',
        'values' => array(
            'module' => 'oft-admin',
            'controller' => 'user',
            'action' => 'reset'
        ),
        'tokens' => array(
        /* 'username' => '(delete|add)',
          'token' => '\d+' */
        )
    ),
    'groups.list' => array(
        'path' => '/admin/groups',
        'method' => array(
            'GET',
            'POST'
        ),
        'values' => array(
            'module' => 'oft-admin',
            'controller' => 'groups',
            'action' => 'index'
        )
    ),
    'groups.edit' => array(
        'path' => '/admin/groups/edit/{id}',
        'method' => array(
            'GET',
            'POST'
        ),
        'values' => array(
            'module' => 'oft-admin',
            'controller' => 'groups',
            'action' => 'edit'
        ),
        'tokens' => array(
            'id' => '\d+'
        )
    ),
    'groups.view' => array(
        'path' => '/admin/groups/view/{id}',
        'method' => array(
            'GET',
            'POST'
        ),
        'values' => array(
            'module' => 'oft-admin',
            'controller' => 'groups',
            'action' => 'view'
        ),
        'tokens' => array(
            'id' => '\d+'
        )
    ),
    'groups.create' => array(
        'path' => '/admin/groups/create',
        'method' => array(
            'GET',
            'POST'
        ),
        'values' => array(
            'module' => 'oft-admin',
            'controller' => 'groups',
            'action' => 'create'
        )
    ),
    'groups.delete' => array(
        'path' => '/admin/groups/delete/{id}',
        'values' => array(
            'module' => 'oft-admin',
            'controller' => 'groups',
            'action' => 'delete'
        ),
        'tokens' => array(
            'id' => '\d+'
        )
    ),
    'resources.list' => array(
        'path' => '/admin/resources',
        'method' => array(
            'GET',
            'POST'
        ),
        'values' => array(
            'module' => 'oft-admin',
            'controller' => 'resources',
            'action' => 'index'
        )
    ),
    'resources.edit' => array(
        'path' => '/admin/resources/edit/{id}',
        'method' => array(
            'GET',
            'POST'
        ),
        'values' => array(
            'module' => 'oft-admin',
            'controller' => 'resources',
            'action' => 'edit'
        ),
        'tokens' => array(
            'id' => '\d+'
        )
    ),
    'resources.view' => array(
        'path' => '/admin/resources/view/{id}',
        'method' => array(
            'GET',
            'POST'
        ),
        'values' => array(
            'module' => 'oft-admin',
            'controller' => 'resources',
            'action' => 'view'
        ),
        'tokens' => array(
            'id' => '\d+'
        )
    ),
    'resources.create' => array(
        'path' => '/admin/resources/create',
        'method' => array(
            'GET',
            'POST'
        ),
        'values' => array(
            'module' => 'oft-admin',
            'controller' => 'resources',
            'action' => 'create'
        )
    ),
    'resources.delete' => array(
        'path' => '/admin/resources/delete/{id}',
        'values' => array(
            'module' => 'oft-admin',
            'controller' => 'resources',
            'action' => 'delete'
        ),
        'tokens' => array(
            'id' => '\d+'
        )
    ),
    'acl.list' => array(
        'path' => '/admin/acl',
        'method' => array(
            'GET',
            'POST'
        ),
        'values' => array(
            'module' => 'oft-admin',
            'controller' => 'acl',
            'action' => 'index'
        )
    ),
    'acl.modify' => array(
        'path' => '/admin/acl/acl-action/{aclAction}/resource/{resourceId}/group/{groupId}',
        'method' => array(
            'GET',
            'POST'
        ),
        'values' => array(
            'module' => 'oft-admin',
            'controller' => 'acl',
            'action' => 'index'
        ),
        'tokens' => array(
            'aclAction' => '(delete|add)',
            'resourceId' => '\d+',
            'groupId' => '\d+'
        )
    ),
);
