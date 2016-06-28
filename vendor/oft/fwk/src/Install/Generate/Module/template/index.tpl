<?php

/* @var $this Oft\View\View */

echo '<?php $this->breadcrumb(__(\'' . $namespace . '\')); ?>' . "\n";
echo '<h1><?=__(\'' . $namespace . '\')?></h1>' . "\n\n";
echo '<p><?=__(\'Page d\\\'accueil du module "%1$s"\', "' . $moduleName . '") ?></p>' . "\n\n";
