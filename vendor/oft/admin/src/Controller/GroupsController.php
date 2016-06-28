<?php
/**
 * Copyright (C) 2015 Orange
 *
 * This software is confidential and proprietary information of Orange.
 * You shall not disclose such Confidential Information and shall use it only
 * in accordance with the terms of the agreement you entered into.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * If you are Orange employee you shall use this software in accordance with
 * the Orange Source Charter (http://opensource.itn.ftgroup/index.php/Orange_Source).
 */

namespace Oft\Admin\Controller;

use DomainException;
use Oft\Admin\Form\DeleteForm;
use Oft\Mvc\ControllerAbstract;
use Oft\Util\Cache;

/**
 * Construction des IHM de manipulation des groupes
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class GroupsController extends ControllerAbstract
{

    /**
     * Container pour la recherche
     *
     * @var mixed
     */
    protected $container;

    /**
     * Règles de filtrage et validation des paramètres
     *
     * @var array
     */
    protected $inputFilterRules = array(
        'field' => array(
            'validators' => array(
                array('name' => 'Regex', 'options' => array('pattern' => '/^[\d\w\- ]+$/u'))
            )
        ),
        'value' => array(
            'validators' => array(
                 array('name' => 'Regex', 'options' => array('pattern' => '/^[\d\w\-]+$/u'))
            )
        )
    );

    /**
     * Initialisation
     */
    public function init()
    {
        $this->container = $this->app->http->session->getContainer('Oft\Admin\Controller\Groups');
        $this->breadcrumb('Groups', $this->smartUrlFromRoute('groups.list'));

        // Accès aux actions du contrôleur pour gérer l'affichage
        // des boutons d'actions et des liens sur les items du tableau
        $this->viewModel->access = array(
            'create' => $this->hasAccessTo('create'),
            'edit' => $this->hasAccessTo('edit'),
            'delete' => $this->hasAccessTo('delete'),
            'view' => $this->hasAccessTo('view'),
        );
    }

    /**
     * IHM de liste des groupes
     */
    public function indexAction()
    {
        $this->breadcrumb('List');

        $service = $this->app->get('GroupsService');

        $data = $this->getDataSearch();
        $search = !empty($data);
        $page = $this->request->getFromQuery('page', 1);

        $urlAutocomplete = $this->smartUrl('auto-complete'). '?field={field}&value={value}';

        $form = $service->getSearchForm($urlAutocomplete);

        if ($this->request->isPost()) {
            $data = $this->request->getFromPost();
            if (isset($data['resetSearch'])) {
                $this->setDataSearch();
                $this->redirectGroupsList();
            }

            $form->setData($data);
            if ($form->isValid()) {
                $data = $form->getData();
                $this->setDataSearch($data);
                $this->redirectToUrl($this->smartUrlFromRoute('groups.list') . '?page=' . $page);
            } else {
                $this->flashMessage("Formulaire invalide", self::WARNING);
            }
        }

        $form->setData($data);

        $paginator = $service->getPaginator($data);
        $paginator->setCurrentPageNumber($page);

        $this->viewModel->form = $form;
        $this->viewModel->search = $search;
        $this->viewModel->paginator = $paginator;
    }

    /**
     * IHM d'édition d'un groupe
     *
     * @param int $groupId
     */
    public function editAction($groupId)
    {
        $this->breadcrumb('Edit');

        $service = $this->app->get('GroupsService');

        try {
            $form = $service->getForm($groupId);
            $posted = false;
            if ($this->request->isPost()) {
                $posted = true;
                $data = $this->request->getFromPost();
                $data['id_acl_role'] = $groupId;

                $form->setData($data);
                if ($form->isValid()) {
                    $service->update($form->getObject());
                    $this->flashMessage('Group modified', self::SUCCESS);
                    
                    Cache::clearCache();
                    
                    $this->redirectGroupsList();
                } else {
                    $this->flashMessage('Invalid Form', self::WARNING);
                }
            }
        } catch (DomainException $e) {
            $this->flashMessage($e->getMessage(), self::WARNING);
            $this->redirectGroupsList();
        }

        $this->viewModel->posted = $posted;
        $this->viewModel->edit = true;
        $this->viewModel->form = $form;
    }

    /**
     * IHM de création d'un groupe
     */
    public function createAction()
    {
        $this->breadcrumb('Create');

        $service = $this->app->get('GroupsService');

        $form = $service->getForm();

        if ($this->request->isPost()) {
            $form->setData($this->request->getFromPost());

            if ($form->isValid()) {
                try {
                    $service->insert($form->getObject());
                    $this->flashMessage('Group created', self::SUCCESS);
                    
                    Cache::clearCache();
                    
                    $this->redirectGroupsList();
                } catch (DomainException $e) {
                    $this->flashMessage($e->getMessage(), self::WARNING);
                }
            } else {
                $this->flashMessage('Invalid Form', self::WARNING);
            }
        }

        $this->viewModel->create = true;
        $this->viewModel->form = $form;
        $this->setTemplate('oft-admin/groups/edit');
    }

    /**
     * IHM de visualisation d'un groupe
     *
     * @param int $groupId
     */
    public function viewAction($groupId)
    {
        $this->breadcrumb('View');

        $service = $this->app->get('GroupsService');

        $form = $service->getFormReadOnly($groupId);

        $this->viewModel->view = true;
        $this->viewModel->form = $form;
        $this->setTemplate('oft-admin/groups/edit');
    }

    /**
     * IHM de suppression d'un groupe
     *
     * @param int $groupId
     */
    public function deleteAction($groupId)
    {
        $this->breadcrumb('Suppression');

        $service = $this->app->get('GroupsService');

        $form = new DeleteForm();
        $form->setData(array('id_acl_role' => $groupId));

        if ($this->request->isPost()) {
            $group = $service->getEntityInstance('group');

            try {
                $service->delete($group, $groupId);
                Cache::clearCache();

                $this->flashMessage('Group deleted', self::SUCCESS);
            } catch (DomainException $e) {
                $this->flashMessage($e->getMessage(), self::WARNING);
            }

            $this->redirectGroupsList();
        }

        $this->viewModel->sprintId = $groupId;
        $this->viewModel->form = $form;
    }

    /**
     * Redirige vers la liste des groupes
     */
    protected function redirectGroupsList()
    {
        $this->redirectToRoute('groups.list');
    }

    /**
     * Met en session les variables de recherche
     *
     * @param array $data
     */
    protected function setDataSearch(array $data = array())
    {
        $this->container->search = $data;
    }

    /**
     * Retourne les valeurs de recherches mises en session si elles sont présentes
     *
     * @return array
     */
    protected function getDataSearch()
    {
        if (isset($this->container->search)) {
            return $this->container->search;
        } else {
            return array();
        }
    }

    /**
     * IHM d'auto-complétion (retour JSON)
     */
    public function autoCompleteAction()
    {
        $this->disableRendering();

        $field = $this->getParam('field');
        $value = $this->getParam('value');
        
        $service = $this->app->get('GroupsService');

        $fields = $service->getFieldsSearch();

        if (!isset($fields[$field]) || !$fields[$field]['autoComplete']) {
            $message = 'Auto-complete refused';
            $this->response->setContent(json_encode($message));

            return;
        }

        $entityKey = $fields[$field]['entity'];

        $fieldName = $fields[$field]['field'];

        try {
            $data = $service->autoComplete($entityKey, $fieldName, $value);

            $this->response->setContent(json_encode($data));
        } catch (DomainException $e) {
            $this->response->setContent(json_encode($e->getMessage()));
        }
    }

}
