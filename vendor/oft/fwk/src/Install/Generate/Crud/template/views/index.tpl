<?php

/* @var $this Oft\View\View */

echo '<?php $this->assets(\'autocomplete\'); ?>' . "\n";

echo '
<div class="row">
    <div class="col-xs-9">
        <h1 class="oft-crud-title"><?= __(\'Liste des éléments\') ?></h1>
    </div>
    <div class="col-xs-3 text-right">
        <?php if ($access[\'create\']) : ?>
        <a  href="<?=$this->smartUrl(\'create\')?>"
            class="btn btn-primary"
            title="<?= __(\'Ajouter un élément\') ?>">
            <span aria-hidden="true" class="glyphicon glyphicon-plus"></span>
            <span class="hidden-xs"> <?= __(\'Ajouter un élément\') ?></span>
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading" style="cursor: pointer;" onclick="$(\'#panel-body-search-role\').toggle();">
        <h3 class="panel-title">
            <span aria-hidden="true" class="glyphicon glyphicon-search"></span>
            <?= __(\'Recherche\') ?>
        </h3>
    </div>
    <div id="panel-body-search-role" class="panel-body" <?=(!$this->hasSearchData ? \' style="display:none;" \' : \'\')?>>
        <?= $this->smartForm($searchForm) ?>
    </div>
</div>

<div class="table-responsive">
<?= $this->datagrid(array(\'' . implode("', '", $primary) . '\'), $this->paginator, $this->columnsOptions, $this->gridOptions) ?>
</div>
';