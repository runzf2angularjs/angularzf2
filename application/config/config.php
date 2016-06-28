<?php

/**
 * Configuration de base
 */
return array(
    // Informations sur l'application
    'application' => array(
        'name' => 'Application Zf2 Angular',
        'contact' => array(
            'name' => 'DTSI/DSI/DevRap/DS D2M/CC PHP',
            'url' => 'mailto:cdc.php@orange.com',
            'mail' => 'cdc.php@orange.com',
        )
    ),

    // Template de présentation général par défaut
    'layout' => array(
        'default' => 'default',
        'path' => '@default/_layout'
    ),

    // Base de donnée
    'db' => include __DIR__ . '/config.db.php',

    // Groupe Identity Referencial
    'gir' => include __DIR__ . '/config.gir.php',

    // Cache configuration
    'cache' => array(
        'dir' => CACHE_DIR,
    ),

);
