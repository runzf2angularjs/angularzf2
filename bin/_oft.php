<?php
require_once __DIR__ . '/../config/bootstrap.php';

if (file_exists(APP_ROOT . '/vendor/oft/fwk/bin/oft')) {
    $binTarget = APP_ROOT . '/vendor/oft/fwk/bin/oft';
} else if (file_exists(APP_ROOT . '/../oft-fwk/bin/oft')) {
    $binTarget = APP_ROOT . '/../oft-fwk/bin/oft';
} else {
    die("OFT not found\n");
}

include $binTarget;
