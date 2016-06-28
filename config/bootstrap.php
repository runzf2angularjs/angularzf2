<?php
// APP ROOT
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

if (getenv('COMPOSER_VENDOR_DIR')) {
    require_once getenv('COMPOSER_VENDOR_DIR') . '/autoload.php';
} else {
    require_once APP_ROOT . '/vendor/autoload.php';
}

// Errors to exception
\Oft\Util\ErrorHandler::register();

// Constants
\Oft\Util\Constants::init();
