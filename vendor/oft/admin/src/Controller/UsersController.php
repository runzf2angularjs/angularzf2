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
use Exception;
use Monolog\Logger;
use Oft\Admin\Form\DeleteForm;
use Oft\Mvc\ControllerAbstract;
use Oft\Mvc\Exception\HttpException;

/**
 * Construction des IHM de gestion des utilisateurs
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class UsersController extends ControllerAbstract
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
        ),
        'term' => array(
            'validators' => array(
                 array('name' => 'Regex', 'options' => array('pattern' => '/^[@\w\s\.]+$/u'))
            ),
            'filters' => array(
                array('name' => 'StripTags'),
            )
        ),
        'complete' => array(
            'validators' => array(),
            'filters' => array(
                array('name' => 'Boolean'),
            )
        )
    );

    /**
     * Initialisation
     */
    public function init()
    {
        // Session
        $this->container = $this->app->http->session->getContainer('Oft\Admin\Controller\Users');

        // Fil d'ariane
        $this->breadcrumb('Users', $this->smartUrlFromRoute('users.list'));

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
     * IHM de liste des utilisateurs
     */
    public function indexAction()
    {
        $this->breadcrumb('List');

        $service = $this->app->get('UsersService');

        $data = $this->getDataSearch();
        $page = $this->request->getFromQuery('page', 1);
        $search = !empty($data);

        $urlAutocomplete = $this->smartUrl('auto-complete'). '?field={field}&value={value}';
        
        $form = $service->getSearchForm($urlAutocomplete);

        if ($this->request->isPost()) {

            $post = $this->request->getFromPost();
            if (isset($data['resetSearch'])) {
                $this->setDataSearch();
                $this->redirectUsersList();
            }

            $form->setData($this->request->getFromPost());

            if ($form->isValid()) {
                $data = $form->getData();
                $this->setDataSearch($data);
                $this->redirectToUrl($this->smartUrlFromRoute('users.list') . '?page=' . $page);
            } else {
                $this->flashMessage('Invalid Form', self::WARNING);
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
     * IHM d'édition d'un utilisateur
     *
     * @param int $idUser
     */
    public function editAction($idUser)
    {
        $this->breadcrumb('Edit');

        $service = $this->app->get('UsersService');

        try {
            $form = $service->getForm($idUser);

            if ($this->app->config['gir']['active']) {
                $form->setAutocomplete($this->smartUrl('search-gir'));
                $this->app->view->assets('oft-admin');
            }
            
            $posted = false;

            if ($this->request->isPost()) {
                $posted = true;
                $data = $this->request->getFromPost();
                $data['id_user'] = $idUser;

                $form->setData($data);
                if ($form->isValid()) {
                    $user = $form->getObject();
                    $userData = $user->getArrayCopy();

                    $service->update($user);

                    oft_trace(
                        'USERS : update',
                        array('username' => $userData['username']),
                        Logger::INFO
                    );

                    $this->flashMessage('User modified', self::SUCCESS);
                    $this->redirectUsersList();
                } else {
                    $this->flashMessage('Invalid Form', self::WARNING);
                }
            }
        } catch (DomainException $e) {
            $this->flashMessage($e->getMessage(), self::WARNING);
            $this->redirectUsersList();
        }

        $this->viewModel->posted = $posted;
        $this->viewModel->edit = true;
        $this->viewModel->form = $form;
    }

    /**
     * IHM de création d'un utilisateur
     */
    public function createAction()
    {
        $this->breadcrumb('Create');

        $service = $this->app->get('UsersService');
        $form = $service->getForm();
        
        if ($this->app->config['gir']['active']) {
            $form->setAutocomplete($this->smartUrl('search-gir'));
            $this->app->view->assets('oft-admin');
        }

        if ($this->request->isPost()) {
            $form->setData($this->request->getFromPost());
            if ($form->isValid()) {
                try {
                    $user = $form->getObject();
                    $userData = $user->getArrayCopy();

                    $service->insert($user);

                    oft_trace(
                        'USERS : create',
                        array(
                            'username' => $userData['username'],
                            'groups' => $userData['groups'],
                        ),
                        Logger::INFO
                    );

                    $this->flashMessage('User created', self::SUCCESS);
                    $this->redirectUsersList();
                } catch (DomainException $e) {
                    $this->flashMessage($e->getMessage(), self::WARNING);
                }
            } else {
                $this->flashMessage('Invalid Form', self::WARNING);
            }
        }

        $this->viewModel->create = true;
        $this->viewModel->form = $form;
        $this->setTemplate('oft-admin/users/edit');
    }

    /**
     * Visualisation d'un utilisateur
     *
     * @param int $idUser
     */
    public function viewAction($idUser)
    {
        $this->breadcrumb('View');

        $service = $this->app->get('UsersService');

        try {
            $form = $service->getFormReadOnly($idUser);
        } catch (DomainException $e) {
            $this->flashMessage($e->getMessage(), self::WARNING);
            $this->redirectUsersList();
        }

        $this->viewModel->view = true;
        $this->viewModel->form = $form;
        $this->setTemplate('oft-admin/users/edit');
    }

    /**
     * Suppression d'un utilisateur
     *
     * @param type $idUser
     */
    public function deleteAction($idUser)
    {
        $this->breadcrumb('Delete');

        $service = $this->app->get('UsersService');
        $form = new DeleteForm();

        if ($this->request->isPost()) {
            $user = $service->getEntityInstance('user');

            try {
                $userData = $user->getArrayCopy();
                $service->delete($user, $idUser);

                oft_trace(
                    'USERS : delete',
                    array('username' => $userData['username']),
                    Logger::INFO
                );

                $this->flashMessage('User deleted', self::SUCCESS);
            } catch (DomainException $e) {
                $this->flashMessage($e->getMessage(), self::WARNING);
            }

            $this->redirectUsersList();
        }

        $this->viewModel->sprintId = $idUser;
        $this->viewModel->form = $form;
    }

    /**
     * Redirige vers la liste des utilisateurs
     */
    protected function redirectUsersList()
    {
        $this->redirectToRoute('users.list');
    }

    /**
     * Met en session les valeurs de recherche
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
        
        $service = $this->app->get('UsersService');
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

    /**
     * IHM de recherche GIR (retour JSON)
     */
    public function searchGirAction()
    {
        $this->disableRendering();

        $response = array();
        $statusCode = 200;
        $headers = array('Content-Type' => 'application/json');

        $term = str_replace(' ', '*', $this->getParam('term'));
        $complete = $this->getParam('complete', false);

        if ($complete) {
            $attributes = array('uid', 'preferredlanguage', 'civility', 'givenname', 'sn', 'mail', 'ftadmou', 'manager');
        } else {
            $attributes = array('ftadmou', 'uid', 'givenname', 'sn');
        }
        
        try {
            $gir = $this->app->get('Gir');
            $result = $gir->findCollaboratorsByUidOrCnOrMail($term, $attributes);
        } catch (Exception $e) {
            $result = false;
            $statusCode = 500;
            oft_exception($e);
        }

        if ($complete && is_array($result) && count($result) == 1) {
            $response[] =
                $result[0]['uid'] . '|' .
                $result[0]['preferredlanguage'] . '|' .
                $result[0]['civility'] . '|' .
                $result[0]['givenname'] . '|' .
                $result[0]['sn'] . '|' .
                $result[0]['mail'] . '|' .
                $result[0]['ftadmou'] . '|' .
                $result[0]['manager'];
        } else if(!$complete && is_array($result) && count($result) > 0) {
            foreach ($result as $people) {
                $response[] =
                    $people['givenname'] . ' ' . $people['sn'] .
                    ' (' . $people['uid'] . ') ' .
                    $people['ftadmou'];
            }
        } else {
            $statusCode = 404;
        }

        throw new HttpException($statusCode, $headers, json_encode($response));
    }

}
