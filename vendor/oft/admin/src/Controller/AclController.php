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
use Monolog\Logger;
use Oft\Mvc\ControllerAbstract;
use Oft\Util\Cache;

/**
 * Construction des IHM de manipulation des permissions d'accès
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class AclController extends ControllerAbstract
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
     * Initialisation     *
     */
    public function init()
    {
        $this->container = $this->app->http->session->getContainer('Oft\Admin\Controller\Acl');
        $this->breadcrumb('Access permissions', $this->smartUrlFromRoute('acl.list'));
    }

    /**
     * IHM de gestion des permissions d'accès
     *
     * @param string $aclAction
     * @param int $resourceId
     * @param int $roleId
     */
    public function indexAction($aclAction = null, $resourceId = null, $roleId = null)
    {
        $this->breadcrumb('List');

        $aclService = $this->app->get('AclService');
        $groupService = $this->app->get('GroupsService');
        $resourceService = $this->app->get('ResourcesService');

        $urlAutocomplete = $this->smartUrl('auto-complete'). '?field={field}&value={value}';
        
        $form = $aclService->getSearchForm($urlAutocomplete);

        if ($this->request->isPost()) {
            // Données postées
            $data = $this->request->getFromPost();

            // RAZ demandée
            if (isset($data['resetSearch'])) {
                $this->setDataSearch();
                $this->redirectAclList();
            }

            // Validation demandée
            if (isset($data['submitSearch'])) {
                $form->setData($data);
                if ($form->isValid()) {
                    $data = $form->getData();
                    $data['resetSearch'] = 'Reset';

                    $this->setDataSearch($data);
                    $this->redirectAclList();
                } else {
                    $this->flashMessage("Formulaire invalide", self::WARNING);
                }
            }
        }

        try {
            if ($resourceId !== null && $roleId !== null) {
                $dataAction = $this->request->getFromPost();
                $dataAction['id_acl_resource'] = $resourceId;
                $dataAction['id_acl_role'] = $roleId;

                if ($aclAction === 'delete') {
                    $formAction = $aclService->getForm($resourceId, $roleId);
                }

                if ($aclAction === 'add') {
                    $formAction = $aclService->getForm();
                    $formAction->setData($dataAction);
                }

                if ($this->request->isPost()) {
                    $formAction->setData($dataAction);

                    if ($formAction->isValid()) {
                        $formAction->setData($this->request->getFromPost());
                        if ($aclAction === 'delete') {
                            $aclService->delete($formAction->getObject(), $resourceId, $roleId);

                            oft_trace(
                                'ACL : changePrivilege',
                                array(
                                    'action' => 'delete',
                                    'resource' => $resourceId,
                                    'role' => $roleId
                                ),
                                Logger::INFO
                            );

                            Cache::clearCache();
                            
                            $this->flashMessage("Règle supprimée", self::SUCCESS);
                            $this->redirectAclList();
                        }

                        if ($aclAction === 'add') {
                            $aclService->insert($formAction->getObject());

                            oft_trace(
                                'ACL : changePrivilege',
                                array(
                                    'action' => 'add',
                                    'resource' => $resourceId,
                                    'role' => $roleId
                                ),
                                Logger::INFO
                            );
                            
                            Cache::clearCache();

                            $this->flashMessage("Règle créée", self::SUCCESS);
                            $this->redirectAclList();
                        }
                    } else {
                        $this->flashMessage('Invalid Form', self::WARNING);
                    }
                }

                $dataRole = $groupService->getById($roleId);
                $dataResource = $resourceService->getById($resourceId);

                $this->viewModel->dataRole = $dataRole;
                $this->viewModel->dataResource = $dataResource;
                $this->viewModel->aclAction = $aclAction;
                $this->viewModel->formAction = $formAction;
            }
        } catch (DomainException $e) {
            $this->flashMessage($e->getMessage(), self::WARNING);
            $this->redirectAclList();
        }

        $data = $this->getDataSearch();
        $search = !empty($data);

        if (isset($data['group']) && $data['group'] !== '') {
            $optionsGroup[] = array(
                'field' => 'fullname',
                'operator' => 'LIKE',
                'value' => $data['group'],
            );

            $roles = $groupService->fetchAllExceptAdmin($optionsGroup);
        } else {
            $roles = $groupService->fetchAllExceptAdmin();
        }

        if (isset($data['resource']) && $data['resource'] !== '') {
            $optionsResource[] = array(
                'field' => 'name',
                'operator' => 'LIKE',
                'value' => $data['resource'],
            );

            $resources = $resourceService->fetchAll($optionsResource);
        } else {
            $resources = $resourceService->fetchAll();
        }

        $acl = $aclService->getListing();

        $form->setData($data);

        $this->viewModel->form = $form;
        $this->viewModel->search = $search;
        $this->viewModel->dataAcl = $acl;
        $this->viewModel->roles = $roles;
        $this->viewModel->resources = $resources;
    }

    /**
     * Redirige vers le listing des ressources
     */
    protected function redirectAclList()
    {
        $this->redirectToRoute('acl.list');
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
     * Retourne les valeurs de la recherche misent en session, si elles sont présentes
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

        $aclService = $this->app->get('AclService');

        $field = $this->getParam('field');
        $value = $this->getParam('value');
        $fields = $aclService->getFieldsSearch();

        if (!isset($fields[$field]) || !$fields[$field]['autoComplete']) {
            $message = 'Auto-complete refused';
            $this->response->setContent(json_encode($message));
            return;
        }

        $entityKey = $fields[$field]['entity'];
        $fieldName = $fields[$field]['field'];
        try {
            $data = $aclService->autoComplete($entityKey, $fieldName, $value);
            $this->response->setContent(json_encode($data));
        } catch (DomainException $e) {
            $this->response->setContent(json_encode($e->getMessage()));
        }
    }

}
