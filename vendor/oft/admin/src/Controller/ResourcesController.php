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
 * Construction des IHM de manipulation des ressources
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class ResourcesController extends ControllerAbstract
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
                 array('name' => 'Regex', 'options' => array('pattern' => '/^[\d\w.\-]+$/u'))
            )
        )
    );

    /**
     * Initialisation
     */
    public function init()
    {
        $this->container = $this->app->http->session->getContainer('Oft\Admin\Controller\Resources');
        $this->breadcrumb('MVC Resources', $this->smartUrlFromRoute('resources.list'));

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
     * IHM de liste des ressources
     */
    public function indexAction()
    {
        $this->breadcrumb('List');

        $service = $this->app->get('ResourcesService');

        $data = $this->getDataSearch();
        $page = $this->request->getFromQuery('page', 1);
        $search = !empty($data);

        $urlAutocomplete = $this->smartUrl('auto-complete'). '?field={field}&value={value}';

        $form = $service->getSearchForm($urlAutocomplete);

        if ($this->request->isPost()) {
            $data = $this->request->getFromPost();

            if (isset($data['resetSearch'])) {
                $this->setDataSearch();
                $this->redirectResourcesList();
            }

            $form->setData($data);

            if ($form->isValid()) {
                $data = $form->getData();
                $this->setDataSearch($data);
                $this->redirectToUrl($this->smartUrlFromRoute('resources.list') . '?page=' . $page);
            } else {
                $this->flashMessage('Invalid Form', self::WARNING);
            }
        }
        
        $form->setData($data);

        if (isset($data['name'])) {
            $data['name'] = '%' . $data['name'] . '%';
        }

        $paginator = $service->getPaginator($data);
        $paginator->setCurrentPageNumber($page);

        $this->viewModel->form = $form;
        $this->viewModel->search = $search;
        $this->viewModel->paginator = $paginator;
    }

    /**
     * IHM d'édition d'une ressource
     *
     * @param int $resourceId
     */
    public function editAction($resourceId)
    {
        $this->breadcrumb('Edit');

        $service = $this->app->get('ResourcesService');

        try {
            $form = $service->getForm($resourceId);
            $posted = false;
            if ($this->request->isPost()) {
                $posted = true;
                $data = $this->request->getFromPost();
                $data['id_acl_resource'] = $resourceId;

                $form->setData($data);

                if ($form->isValid()) {
                    $service->update($form->getObject());
                    $this->flashMessage('Resource modified', self::SUCCESS);
                    
                    Cache::clearCache();
                    
                    $this->redirectResourcesList();
                } else {
                    $this->flashMessage('Invalid Form', self::WARNING);
                }
            }
        } catch (DomainException $e) {
            $this->flashMessage($e->getMessage(), self::WARNING);
            $this->redirectResourcesList();
        }

        $this->viewModel->posted = $posted;
        $this->viewModel->edit = true;
        $this->viewModel->form = $form;
    }

    /**
     * IHM de création d'une ressource
     */
    public function createAction()
    {
        $this->breadcrumb('Create');

        $service = $this->app->get('ResourcesService');
        $form = $service->getForm();

        if ($this->request->isPost()) {
            $form->setData($this->request->getFromPost());

            if ($form->isValid()) {
                try {
                    $service->insert($form->getObject());
                    $this->flashMessage('Resource created', self::SUCCESS);
                    
                    Cache::clearCache();
                    
                    $this->redirectResourcesList();
                } catch (DomainException $e) {
                    $this->flashMessage($e->getMessage(), self::WARNING);
                }
            } else {
                $this->flashMessage('Invalid Form', self::WARNING);
            }
        }

        $this->viewModel->create = true;
        $this->viewModel->form = $form;
        $this->setTemplate('oft-admin/resources/edit');
    }

    /**
     * IHM de visualisation d'une ressource
     *
     * @param int $resourceId
     */
    public function viewAction($resourceId)
    {
        $this->breadcrumb('View');

        $service = $this->app->get('ResourcesService');
        $form = $service->getFormReadOnly($resourceId);

        $this->viewModel->view = true;
        $this->viewModel->form = $form;
        $this->setTemplate('oft-admin/resources/edit');
    }

    /**
     * IHM de suppression d'une ressource
     *
     * @param int $resourceId
     */
    public function deleteAction($resourceId)
    {
        $this->breadcrumb('Delete');

        $service = $this->app->get('ResourcesService');

        $form = new DeleteForm();
        $form->setData(array('id_acl_resource' => $resourceId));

        if ($this->request->isPost()) {
            $resource = $service->getEntityInstance('resource');
            try {
                $service->delete($resource, $resourceId);
                Cache::clearCache();
                
                $this->flashMessage('Resource deleted', self::SUCCESS);
            } catch (DomainException $e) {
                $this->flashMessage($e->getMessage(), self::WARNING);
            }

            $this->redirectResourcesList();
        }

        $this->viewModel->form = $form;
    }

    /**
     * Redirige vers la liste des ressources
     */
    protected function redirectResourcesList()
    {
        $this->redirectToRoute('resources.list');
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

        $resourcesService = $this->app->get('ResourcesService');

        $fields = $resourcesService->getFieldsSearch();

        if (!isset($fields[$field]) || !$fields[$field]['autoComplete']) {
            $message = 'Auto-complete refused';
            $this->response->setContent(json_encode($message));

            return;
        }

        $entityKey = $fields[$field]['entity'];

        $fieldName = $fields[$field]['field'];

        try {
            $data = $resourcesService->autoComplete($entityKey, $fieldName, $value);

            $this->response->setContent(json_encode($data));
        } catch (DomainException $e) {
            $this->response->setContent(json_encode($e->getMessage()));
        }
    }

}
