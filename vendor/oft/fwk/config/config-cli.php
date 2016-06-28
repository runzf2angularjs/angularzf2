<?php

return array(
    'commands' => array(
        // Commandes du framework
        //  - DB
        'Oft\Console\Command\DbTablesCommand',

        // Commandes du service d'installation
        //  - Générateurs
        'Oft\Install\Command\GenerateRepositoryCommand',
        'Oft\Install\Command\GenerateCrudCommand',
        'Oft\Install\Command\GenerateModuleCommand',
        //  - Migrations DB
        'Oft\Install\Command\DbGenerateCommand',
        'Oft\Install\Command\DbMigrateCommand',
        //  - DB
        'Oft\Install\Command\DbConfigCommand',
        'Oft\Install\Command\DbStatusCommand',
        'Oft\Install\Command\DbSchemaCommand',
        //  - Configuration GIR
        'Oft\Install\Command\GirConfigCommand',
        //  - Cache
        'Oft\Install\Command\ClearCacheCommand',
        //  - Admin
        'Oft\Install\Command\AdminAddUserCommand',
        'Oft\Install\Command\AdminDeleteUserCommand',
    ),
    'services' => array(
        'DoctrineMigrations' => 'Oft\Install\Service\Provider\DoctrineMigrations',
    ),
    'migrations' => array(
        'table' => 'oft_migrations',
    ),
);
