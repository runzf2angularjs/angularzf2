<?php

/* @var $this Oft\View\View */

echo "<?php\n";
?>
if (!file_exists(__DIR__ . '/../../../vendor/autoload.php')) {
    die("[ERROR] No vendor/autoload.php found !\n");
}

$autoloader = include __DIR__ . '/../../../vendor/autoload.php';

$autoloader->addPsr4('<?=$namespace?>\\Test\\', __DIR__ . '/src');
