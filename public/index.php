<?php
require_once __DIR__ . '/../config/bootstrap.php';

// Récupération de la configuration principale (depuis APP_ENV si elle est définie)
if (defined('APP_ENV')) {
    $mainConfig = include __DIR__ . '/../config/app.' . APP_ENV . '.php';
} else {
    $mainConfig = include __DIR__ . '/../config/app.php';
}

try {
    $application = new \Oft\Mvc\Application($mainConfig);
    $application->run()
        ->send();
} catch (Exception $e) {
    if (!headers_sent()) {
        header('HTTP/1.1 500 Server Error');
    }
    echo "Une erreur technique empêche le site de fonctionner correctement";
    oft_exception($e);
}
