<?php

/**
 * Modèle de la classe repository
 */

/* @var $this Oft\View\View */

$editUrlParams = '?';
foreach ($primary as $column) {
    $editUrlParams .= $column . '=\' . e($entity->' . $column . ') . \'' . '&';
}
$editUrlParams = substr($editUrlParams, 0, -4);

echo '<h1><?= __(\'Visualiser un élément\') ?></h1>

<div class="table-responsive">
    <table class="table table-striped table-hover table-condensed">
        <thead>
            <tr>
                <th width="50%"><?= __(\'Libellé\') ?></th>
                <th><?= __(\'Valeur\') ?></th>
            </tr>
        </thead>
        <tbody>';
foreach ($columns as $column => $data) {
    echo '
            <tr>
                <th>' . $column . '</th>';
    switch ($data['type']) {
        case 'float' :
            echo '
                <td><?=e($entity->' . $column . ')?></td>';
            break;
        case 'datetime' :
        case 'timestamp' :
            echo '
                <td><?=e($this->dateFormatter($entity->' . $column . '))?></td>';
            break;
        case 'date' :
            echo '
                <td><?=e($this->dateFormatter($entity->' . $column . ', \'short\', \'none\', \'sql\', \'none\'))?></td>';
            break;
        case 'time' :
            echo '
                <td><?=e($this->dateFormatter($entity->' . $column . ', \'none\', \'medium\', \'none\', \'sql\'))?></td>';
            break;
        default :
            echo '
                <td><?=e($entity->' . $column . ')?></td>';
        }
    echo '
            </tr>';
}

echo '
        </tbody>
    </table>
</div>

<div class="form-group pull-right">
    <?php if($this->access[\'edit\']) : ?>
    <a
        class="btn btn-primary"
        role="button"
        href="<?=$this->smartUrl(\'edit\') . \'' . $editUrlParams . '?>">
        <?= __(\'Modifier\') ?>
    </a>
    <?php endif; ?>
    <a class="btn btn-default" role="button" href="<?=$this->smartUrl(\'index\')?>"><?= __(\'Retour\') ?></a>
</div>
';