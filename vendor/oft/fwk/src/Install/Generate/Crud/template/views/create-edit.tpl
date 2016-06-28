<?php

/**
 * Modèle de la classe repository
 */

/* @var $this Oft\View\View */

$editUrlParams = '?';
foreach ($primary as $column) {
    $editUrlParams .= $column . '=\' . $form->get(\'' . $column . '\')->getValue() . \'' . '&';
}
$editUrlParams = substr($editUrlParams, 0, -4);

echo '
<?php if ($action == \'create\') : ?>
<h1><?= __(\'Créer un élément\') ?></h1>
<?php elseif ($action == \'edit\') : ?>
<h1><?= __(\'Modifier un élément\') ?></h1>
<?php endif; ?>

<?=$this->smartForm($form)?>

<div class="form-group pull-right">
    <a class="btn btn-default" role="button" href="<?=$this->smartUrl(\'index\')?>"><?= __(\'Retour\') ?></a>
</div>
';