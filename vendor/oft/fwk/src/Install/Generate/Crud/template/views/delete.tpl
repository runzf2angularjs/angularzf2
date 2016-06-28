<?php

/**
 * Modèle de la classe repository
 */

/* @var $this Oft\View\View */

$deleteUrlParams = '?';
foreach ($primary as $column) {
    $deleteUrlParams .= $column . '=\' . e($' . $column . ') . \'' . '&';
}
$deleteUrlParams = substr($deleteUrlParams, 0, -4);

echo '
<h1><?= __(\'Supprimer un élément\') ?></h1>

<p><?= __(\'En cliquant sur le lien "Supprimer", les données de cet élément vont être irrémédiablement perdues.\') ?></p>
<p><?= __(\'Assurez-vous de vraiment vouloir supprimer cet élément avant de cliquer sur le bouton "Supprimer".\') ?></p>

<p class="text-center">
    <a
        class="btn btn-danger"
        role="button"
        href="<?=$this->smartUrl(\'delete\') . \'' . $deleteUrlParams . '. \'&confirm=1\'?>">
        <?= __(\'Supprimer\') ?>
    </a>
    &nbsp;
    <a class="btn btn-default" role="button" href="<?=$this->smartUrl(\'index\')?>"><?= __(\'Retour\') ?></a>
</p>
';