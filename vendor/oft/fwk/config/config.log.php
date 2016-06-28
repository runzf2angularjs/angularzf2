<?php

return array(
    // Canal de log par défaut
    'default' => array(
        'filename' => LOG_DIR . '/default.log',
        'format' => array(
            'filename' => '{date}-{filename}',
            'date' => 'Y-m-d',
        ),
        'level' => \Monolog\Logger::NOTICE,
    ),
    // Canal de log de sécurité pour l'OFT
    'security' => array(
        'filename' => LOG_DIR . '/security.log',
        'format' => array(
            'filename' => '{date}-{filename}',
            'date' => 'Y-m-d',
        ),
        'level' => \Monolog\Logger::INFO,
    ),
);
