<?php

return array(
    'active' => false,
    'ldap' => array(
        'host' => 'ldap-preprod.com.ftgroup',
        'username' => 'uid=[IDENT],ou=accounts,dc=intrannuaire,dc=orange,dc=com',
        'password' => '[PASSWORD]',
        'baseDn' => 'ou=people,dc=intrannuaire,dc=orange,dc=com',
        'port' => 30002,
        'useSsl' => false,
    )
);