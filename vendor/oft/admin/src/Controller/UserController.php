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
use Oft\Admin\Form\ForgotForm;
use Oft\Mvc\ControllerAbstract;

/**
 * Construction des IHM de l'utilisateur
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class UserController extends ControllerAbstract
{

    /**
     * Initialisation
     */
    public function init()
    {
        $this->breadcrumb('User', $this->smartUrlFromRoute('user.profile'));
    }

    /**
     * IHM de profil de l'utilisateur
     */
    public function profileAction()
    {
        $this->breadcrumb('Profile');

        $service = $this->app->get('UsersService');

        $this->viewModel->identity = $this->app->identity->get();
        $this->viewModel->civilities = $service->getCivilities();
    }

    /**
     * IHM de changement de mot de passe
     */
    public function changeAction()
    {
        $this->breadcrumb('Change password');

        $identity = $this->app->identity->get();
        $service = $this->app->get('UsersService');

        if ($identity->isGuest()) {
            $this->redirectToRoute('auth.login');
        }

        $form = $service->getFormPassword();
        if ($this->request->isPost()) {
            $form->setData($this->request->getFromPost());
            if ($form->isValid()) {
                try {
                    $data = $form->getData();
                    $username = $data['username'];
                    $oldPassword = $data['password'];
                    $newPassword = $data['new_password'];

                    $service->changePassword($username, $oldPassword, $newPassword);

                    oft_trace(
                        'USER : changePassword',
                        array('username' => $username),
                        Logger::INFO
                    );

                    $this->flashMessage('Password changed', self::SUCCESS);
                } catch (DomainException $e) {
                    $this->flashMessage($e->getMessage(), self::WARNING);
                }
            } else {
                $this->flashMessage('Invalid Form', self::WARNING);
            }
        }

        $this->viewModel->form = $form;
    }

    /**
     * IHM de demande de réinitialisation de mot de passe
     */
    public function forgotAction()
    {
        $this->breadcrumb('Forgot password');

        $service = $this->app->get('UsersService');
        $form = new ForgotForm();

        if ($this->request->isPost()) {
            $form->setData($this->request->getFromPost());
            if ($form->isValid()) {
                try {
                    $data = $form->getData();
                    $username = $data['username'];

                    $service->forgotPassword($username);

                    oft_trace(
                        'USER : forgotPassword',
                        array('username' => $username,),
                        Logger::INFO
                    );

                    $this->flashMessage('An email has been sent', self::SUCCESS);
                    $this->redirectToRoute('auth.login');
                } catch (DomainException $e) {
                    $this->flashMessage($e->getMessage(), self::WARNING);
                }
            } else {
                $this->flashMessage('Invalid Form', self::WARNING);
            }
        }

        $this->viewModel->forgot = true;
        $this->viewModel->form = $form;
        $this->setTemplate('oft-admin/user/password');
    }

    /**
     * IHM de réinitialisation de mot de passe
     *
     * @param string $username
     * @param string $token
     */
    public function resetAction($username = null, $token = null)
    {
        $this->breadcrumb('Reset password');

        $service = $this->app->get('UsersService');

        try {
            $isValid = $service->isValidToken($username, $token);

            if (!$isValid) {
                $this->flashMessage('This link is not valid', self::WARNING);
                $this->redirectToRoute('user.forgot');
            }
        } catch (DomainException $e) {
            $this->flashMessage($e->getMessage(), self::WARNING);
            $this->redirectToRoute('user.forgot');
        }

        $form = $service->getFormPasswordReset($username);

        if ($this->request->isPost()) {
            $data = $this->request->getFromPost();

            $form->setData($data);
            if ($form->isValid()) {
                try {
                    $data = $form->getData();
                    $password = $data['new_password'];

                    $service->changePassword($username, null, $password, true);

                    oft_trace(
                        'USER : resetPassword',
                        array(
                            'username' => $username,
                            'token' => $token,
                        ),
                        Logger::INFO
                    );

                    $this->flashMessage('Password changed', self::SUCCESS);
                    $this->redirectToRoute('auth.login');
                } catch (DomainException $e) {
                    $this->flashMessage($e->getMessage(), self::WARNING);
                }
            } else {
                $this->flashMessage('Invalid Form', self::WARNING);
            }
        }

        $this->viewModel->reset = true;
        $this->viewModel->form = $form;
        $this->setTemplate('oft-admin/user/password');
    }

}
