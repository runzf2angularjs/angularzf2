<?php
$mainConfig = include __DIR__ . '/app.php';

// Activation du mode "debug"
$mainConfig['debug'] = true;

// Chargement du module des outils de développement
$mainConfig['modules'][] = 'Oft\Debug';

// Définition du niveau de log pour le mode développement
$mainConfig['log']['default']['level'] = \Monolog\Logger::DEBUG;
$mainConfig['log']['security']['level'] = \Monolog\Logger::DEBUG;

return $mainConfig;
