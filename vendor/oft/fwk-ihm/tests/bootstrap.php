<?php
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    $autoloader = include __DIR__ . '/../vendor/autoload.php';
} else if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    $autoloader = include __DIR__ . '/../../vendor/autoload.php';
} else {
    die("[ERROR] No vendor/autoload.php found !\n");
}

$autoloader->addPsr4('Oft\\Ihm\\Test\\', __DIR__ . '/src');
