<?php

/* @var $this Oft\View\View */

echo "<?php\n";
?>

namespace <?=$namespace?>;

use <?=$formFullClassName?>;
use <?=$searchFormFullClassName?>;
use <?=$repositoryFullClassName?>;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Oft\Entity\BaseEntity;
use Oft\Http\Session;
use Oft\Mvc\ControllerAbstract;
use Oft\Mvc\Exception\NotFoundException;
use Zend\Validator\InArray;

class <?=$className?> extends ControllerAbstract
{

    /**
     * @var <?=$repositoryClassName?>
     */
    protected $repository;

    /**
     * @var Session
     */
    protected $sessionContainer;

    /**
     * Champs sur lesquels l'auto-complétion est activée
     * dans le formulaire de recherche
     *
     * @var array
     */
    protected $autocompleteFields = array(<?php
        foreach ($metadata as $column => $info) :
            if (in_array($column, $primary)) {
                continue;
            }

            if (in_array($info['type'], array('date', 'datetime', 'time', 'timestamp'))) {
                continue;
            }

            echo "\n" . '        \'' . $column . '\',';
        endforeach;
        echo "\n";
    ?>
    );

    /**
     * Paramètres : filtrage & validation
     *
     * @var array
     */
    protected $inputFilterRules = array( <?php
        foreach ($primary as $column) :
            echo
                "\n" .
                '        \'' . $column . '\' => array(' . "\n" .
                '            \'validators\' => array(';

            if (preg_match('/char/', $metadata[$column]['type'])) {
                echo
                    "\n" .
                    '                array(' . "\n" .
                    '                    \'name\' => \'StringLength\',' . "\n" .
                    '                    \'options\' => array(\'max\' => ' . $metadata[$column]['length'] . '),' . "\n" .
                    '                ),';
            }

            if (preg_match('/int|decimal|float/', $metadata[$column]['type'])) {
                echo "\n" . '                array(\'name\' => \'Digits\'),';
            }

            echo "\n" . '            ),';
            echo "\n" . '        ),';
        endforeach;
        echo "\n";
        ?>
        'confirm' => array(
            'filters' => array(
                array('name' => 'Boolean'),
            ),
        ),
        'page' => array(
            'filters' => array(
                array('name' => 'Int'),
            ),
        ),
        'sort' => array(
            'validators' => array(/* définis dynamiquement */),
        ),
        'order' => array(
            'validators' => array(
                array(
                    'name' => 'InArray',
                    'options' => array('haystack' => array('desc', 'asc')),
                ),
            ),
        ),
        'field' => array(
            'required' => true,
            'validators' => array(/* définis dynamiquement */),
        ),
        'value' => array(
            'required' => true,
        ),
    );

    /**
     * Méthode d'initialisation exécutée par le constructeur
     */
    public function init()
    {
        $this->repository = new <?=$repositoryClassName?>($this->app);

        // Flag d'accès aux actions du contrôleur pour gérer l'affichage
        // des boutons d'actions et des liens dans les vues
        $this->viewModel->access = array(
            'view' => $this->hasAccessTo('view'),
            'create' => $this->hasAccessTo('create'),
            'edit' => $this->hasAccessTo('edit'),
            'delete' => $this->hasAccessTo('delete'),
        );

        // Fil d'ariane
        $this->breadcrumb('<?=$crudName?>', $this->smartUrl('index'));
    }

    /**
     * Règles de validation des paramètres
     */
    public function getInputFilterRules()
    {
        $validColumns = array_keys(<?=$repositoryClassName?>::$metadata);

        $this->inputFilterRules['sort']['validators'] = array(
            new InArray(array('haystack' => $validColumns))
        );

        $this->inputFilterRules['field']['validators'] = array(
            new InArray(array('haystack' => $validColumns))
        );

        return parent::getInputFilterRules();
    }

    /**
     * Liste des éléments
     */
    public function indexAction()
    {
        $this->breadcrumb('liste');

        $currentPageNumber = $this->getParam('page', 0);

        $searchForm = new <?=$searchFormClassName?>($this->getSearchEntity());

        $autocompleteUrl = $this->smartUrl('autocomplete') . '?field={field}&value={value}';
        foreach ($this->autocompleteFields as $field) {
            $searchForm->setAutocompleteField($field, $autocompleteUrl);
        }

        if ($this->isPost()) {
            $this->handleSearchForm($searchForm);
        }

        $filters = $searchForm->getSearchData();
        $sort = $this->getParam('sort', '<?=$primary[0]?>');
        $order = $this->getParam('order', 'asc');

        $options = array(
            'filters' => $filters,
            'sort' => array($sort => $order),
        );

        $paginator = $this->repository->getPaginator($options);

        return array(
            'searchForm' => $searchForm,
            'hasSearchData' => count($filters) ? true : false,
            'paginator' => $paginator,
            'gridOptions' => $this->getGridOptions($sort, $order, $currentPageNumber),
            'columnsOptions' => $this->getColumnsOptions(),
        );
    }

    /**
     * Visualisation d'un élément
     */
    public function viewAction()
    {
        $this->breadcrumb('visualiser');

<?php
    foreach ($primary as $column) :
        echo '        $' . $column . ' = $this->getParam(\'' . $column . '\', null);' . "\n";
        echo '        if ($' . $column . ' === null) { ' . "\n";
        echo '            throw new NotFoundException();' . "\n";
        echo '        }' . "\n";
    endforeach;
?>

        return array(
            'entity' => $this->repository->getById(<?php
                $args = '';
                foreach ($primary as $column) :
                    $args .= '$' . $column . ', ';
                endforeach;
                echo substr($args, 0, -2);
            ?>),
        );
    }

    /**
     * Création d'un élément
     */
    public function createAction()
    {
        $this->breadcrumb('créer');

        $entity = $this->repository->getNew();

        $form = new <?=$formClassName?>($entity);
        $form->setCreateForm();

        if ($this->request->isPost()) {
            $form->setData($this->request->getFromPost());
            if ($form->isValid()) {
                try {
                    $this->repository->insert($entity);
                    $this->flashMessage('L\'élément a été créé avec succès', self::SUCCESS);
                    $this->redirect('index');
                } catch (DBALException $e) {
                    $this->flashMessage('L\'élément n\'a pas pu être créé', self::WARNING);
                }
            } else {
                $this->flashMessage('La saisie est invalide', self::WARNING);
            }
        }

        $this->setTemplate('<?= $moduleName ?>/<?= $controllerName ?>/create-edit');

        return array(
            'action' => 'create',
            'form' => $form,
        );
    }

    /**
     * Edition d'un élément
     */
    public function editAction()
    {
        $this->breadcrumb('modifier');

<?php
    foreach ($primary as $column) :
        echo '        $' . $column . ' = $this->getParam(\'' . $column . '\', null);' . "\n";
        echo '        if ($' . $column . ' === null) { ' . "\n";
        echo '            throw new NotFoundException();' . "\n";
        echo '        }' . "\n";
    endforeach;
?>

        $entity = $this->repository->getById(<?php
                $args = '';
                foreach ($primary as $column) :
                    $args .= '$' . $column . ', ';
                endforeach;
                echo substr($args, 0, -2);
            ?>);
        $form = new <?=$formClassName?>($entity);
        $form->setUpdateForm();

        if ($this->request->isPost()) {
            $taintedData = $this->request->getFromPost();
            $form->setData($taintedData);
            if ($form->isValid()) {
                try {
                    $this->repository->update($entity);
                    $this->flashMessage('L\'élément a été modifié avec succès', self::SUCCESS);
                    $this->redirect('index');
                } catch (ForeignKeyConstraintViolationException $e) {
                    $this->flashMessage('L\'élément n\'a pas pu être modifié en raison d\'une contrainte d\'intégrité', self::WARNING);
                } catch (DBALException $e) {
                    $this->flashMessage('L\'élément n\'a pas pu être modifié', self::WARNING);
                }
            }
        }

        $this->setTemplate('<?= $moduleName ?>/<?= $controllerName ?>/create-edit');

        return array(
            'action' => 'edit',
            'form' => $form,
        );
    }

    /**
     * Suppression d'un élément
     */
    public function deleteAction()
    {
        $this->breadcrumb('supprimer');

<?php
    foreach ($primary as $column) :
        echo '        $' . $column . ' = $this->getParam(\'' . $column . '\', null);' . "\n";
        echo '        if ($' . $column . ' === null) { ' . "\n";
        echo '            throw new NotFoundException();' . "\n";
        echo '        }' . "\n";
    endforeach;
?>

        if ($this->getParam('confirm')) {
            try {
                $entity = $this->repository->getById(<?php
                    $args = '';
                    foreach ($primary as $column) :
                        $args .= '$' . $column . ', ';
                    endforeach;
                    echo substr($args, 0, -2);
                ?>);
                $this->repository->delete($entity);
                $this->flashMessage('L\'élément a été supprimé avec succès', self::SUCCESS);
            } catch (ForeignKeyConstraintViolationException $e) {
                $this->flashMessage('L\'élément n\'a pas pu être supprimé en raison d\'une contrainte d\'intégrité', self::WARNING);
            } catch (DBALException $e) {
                $this->flashMessage('L\'élément n\'a pas pu être supprimé', self::WARNING);
            }
            $this->redirect('index');
        }

        return array( <?php
            foreach ($primary as $column) :
                echo "\n" . '            \'' . $column . '\' => $' . $column . ',';
            endforeach;
            echo "\n";
        ?>
        );
    }

    /**
     * Autocomplétion
     */
    public function autocompleteAction()
    {
        $this->disableRendering();

        $field = $this->getParam('field');
        $value = $this->getParam('value');

        $filters = array(
            array(
                'field' => $field,
                'value' => $value,
                'operator' => 'LIKE'
            )
        );

        $queryBuilder = $this->repository->getQueryBuilder(array('filters' => $filters), array($field));
        $results = $queryBuilder->execute();

        $jsonResponse = array();
        foreach ($results as $result) {
            $jsonResponse[] = $result[$field];
        }

        $this->response->setContentType('application/json');
        $this->response->setContent(json_encode($jsonResponse));
    }

    /**
     * Prise en charge du formulaire de recherche
     *
     * @param <?=$searchFormClassName?> $searchForm
     */
    protected function handleSearchForm(<?=$searchFormClassName?> $searchForm)
    {
        $taintedPostData = $this->request->getFromPost();

        if (isset($taintedPostData['reset_search'])) {
            unset($this->getSessionContainer()->search);
            $searchForm->setData(array());

            $this->redirect('index');
        }

        $searchForm->setData($taintedPostData);

        if (!$searchForm->isValid()) {
            $this->flashMessage('Formulaire de recherche invalide', self::WARNING);
        } else {
            $this->getSessionContainer()->search = $searchForm->getObject();
        }
    }

    /**
     * Retourne les options du tableau de l'IHM de liste
     *
     * @param string $sort
     * @param string $order
     * @return array
     */
    protected function getGridOptions($sort, $order, $page)
    {
        $options = array(
            'sort' => $sort,
            'order' => $order,
            'page' => $page,
            'itemPerPage' => 10
        );

        if ($this->viewModel->access['view']) {
            $options['linkOn'] = array(<?php
foreach (array_keys($metadata) as $column) :
    if (in_array($column, $primary)) {
        continue;
    }
    echo "\n" . '                \'' . $column . '\',';
endforeach;
echo "\n" . '            ';
            ?>);

            $options['link'] = array(
                'action' => 'view',
            );
        }

        if ($this->viewModel->access['edit']) {
            $options['actions']['edit'] = array(
                'content' => '<span class="btn glyphicon glyphicon-cog"></span>',
                'link' => array(
                    'action' => 'edit',
                ),
            );
        }

        if ($this->viewModel->access['delete']) {
            $options['actions']['delete'] = array(
                'content' => '<span class="btn glyphicon glyphicon-trash"></span>',
                'link' => array(
                    'action' => 'delete',
                ),
            );
        }

        return $options;
    }

    /**
     * Retourne les options des colonnes du tableau de l'IHM de liste
     *
     * @return array
     */
    protected function getColumnsOptions()
    {
        return array(<?php
foreach (array_keys($metadata) as $column) :
    // Valeurs par défaut des colonnes
    $name = $column;
    $width = '';
    $sortable = "\n" . '                \'sortable\' => true,';
    $displayAs = '';
    $type = $metadata[$name]['type'];

    // Pour les clefs primaires
    if (in_array($name, $primary)) { // SI PRIMAIRE
        $width = "\n" . '                \'width\' => \'60px\',';
        $sortable = '';
        if (count($primary) === 1) { // SI QU'UNE SEULE PRIMAIRE
            $name = '#';
        }
    }

    if (in_array($type, array('datetime', 'timestamp'))) {
        $displayAs = "\n" . '                \'display_as\' => \'datetime\',';
    }
    if (in_array($type, array('time', 'date'))) {
        $displayAs = "\n" . '                \'display_as\' => \''.$type.'\',';
    }

    echo
        "\n" . '            \'' . $column . '\' => array(' .
        "\n" . '                \'name\' => \'' . $name . '\',' .
        $width .
        $sortable .
        $displayAs .
        "\n" . '            ),';
endforeach;
echo "\n";
?>
            'actions' => array(
                'width' => '100px',
            ),
        );
    }

    /**
     * Retourne l'entité de la recherche
     *
     * @return BaseEntity
     */
    protected function getSearchEntity()
    {
        if (isset($this->getSessionContainer()->search)
            && $this->getSessionContainer()->search instanceof BaseEntity
        ) {
            return $this->getSessionContainer()->search;
        }

        return $this->repository->getNewEmpty();
    }

    /**
     * Retourne l'espace de stockage persistant
     *
     * @return Session
     */
    protected function getSessionContainer()
    {
        if ($this->sessionContainer === null) {
            $this->sessionContainer = $this->session->getContainer(get_class($this));
        }

        return $this->sessionContainer;
    }
}
