<?php
require_once __DIR__ . '/../config/bootstrap.php';

$env = strtolower(basename(__FILE__, '.php'));

if (defined('APP_ENV') && APP_ENV != $env) {
    die("La configuration de l'environnement est incohérente");
}

if (!defined('APP_ENV')) {
    define('APP_ENV', $env);
}

if (!file_exists(__DIR__ . '/../config/app.' . APP_ENV . '.php')) {
    die("Le fichier de configuration de l'environnement n'existe pas");
}

$mainConfig = include __DIR__ . '/../config/app.' . APP_ENV . '.php';

try {
    $application = new \Oft\Mvc\Application($mainConfig);
    $application->run()
        ->send();

} catch (Exception $e) {
    $whoops = new \Whoops\Run();
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
    $whoops->handleException($e);
}
