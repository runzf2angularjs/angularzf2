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

namespace Oft\Admin\Service;

use DateTime;
use Doctrine\DBAL\Connection;
use DomainException;
use Oft\Admin\Form\PasswordForm;
use Oft\Admin\Form\SearchForm;
use Oft\Admin\Form\UserForm;
use Oft\Auth\Identity;
use Oft\Entity\BaseEntity;
use Oft\Entity\UserEntity;
use Oft\Mvc\Application;
use Oft\Mvc\View;
use Oft\Paginator\Adapter\QueryBuilder as QueryBuilderAdapter;
use Zend\I18n\Translator\Translator;
use Zend\Mail\Message as MailMessage;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Paginator\Paginator;
use Zend\Validator\EmailAddress;
use Zend\View\Model\ViewModel;
use Zend\View\Resolver\TemplateMapResolver;

/**
 * Service pour la gestion des utilisateurs
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class UsersService
{

    /**
     * @var Connection
     */
    protected $db;

    /**
     * Entités
     *
     * @var array
     */
    protected $entityClassesName = array(
        'user' => '\Oft\Entity\UserEntity',
        'group' => '\Oft\Entity\GroupEntity'
    );

    /**
     * Classe de transport pour l'envoi de mail
     * 
     * @var string
     */
    protected $transport = 'Zend\Mail\Transport\Sendmail';

    /**
     * @var Identity
     */
    protected $identity;

    /**
     * @var View
     */
    protected $view;

    /**
     * @var array
     */
    protected $appConfig;
    
    /**
     * @var Translator 
     */
    protected $translator;

    /**
     * Défini les champs sur lesquels la recherche est autorisée
     *
     * @var array
     */
    protected $fieldsSearch = array(
        'username' => array(
            'entity' => 'user',
            'field' => 'username',
            'autoComplete' => true,
        ),
        'givenname' => array(
            'entity' => 'user',
            'field' => 'givenname',
            'autoComplete' => true,
        ),
        'surname' => array(
            'entity' => 'user',
            'field' => 'surname',
            'autoComplete' => true,
        ),
        'active' => array(
            'entity' => 'user',
            'field' => 'active',
            'autoComplete' => false,
        ),
    );

    /**
     * Construction
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->identity = $app->get('Identity')->get();
        $this->db = $app->get('Db');
        $this->view = $app->get('View');
        $this->appConfig = $app->config['application'];
        $this->translator = $app->get('Translator');
    }

    /**
     * Retourne l'instance de l'entité demandée
     *
     * @param string $name
     * @return BaseEntity
     */
    public function getEntityInstance($name)
    {
        return new $this->entityClassesName[$name]($this->db);
    }

    /**
     * Enregistre une classe de transport pour l'envoi d'e-mail
     *
     * @param string $className
     */
    public function setTransportClassName($className)
    {
        $this->transport = $className;
    }

    /**
     * Enregistre une classe d'entité
     *
     * @param string $name
     * @param string $className
     */
    public function setEntityClassName($name, $className)
    {
        $this->entityClassesName[$name] = $className;
    }

    /**
     * Retourne la configuration des champs sur lesquels la recherche est autorisée
     *
     * @return array
     */
    public function getFieldsSearch()
    {
        return $this->fieldsSearch;
    }

    /**
     * Retourne la liste des utilisateurs paginée
     *
     * @param array $data
     * @return Paginator
     */
    public function getPaginator(array $data = array())
    {
        $whereOptions = array();
        $entity = $this->getEntityInstance('user');

        foreach ($this->fieldsSearch as $field => $config) {
            if (isset($data[$field]) && $data[$field] != '') {
                $whereOptions[] = array(
                    'field' => $field,
                    'operator' => 'LIKE',
                    'value' => $data[$field],
                );
            }
        }

        $queryBuilder = $entity->getQueryBuilder($whereOptions);
        $adapter = new QueryBuilderAdapter(
            $queryBuilder,
            $this->entityClassesName['user']
        );

        return new Paginator($adapter);
    }

    /**
     * Retourne le formulaire de recherche
     *
     * @return SearchForm
     */
    public function getSearchForm($url = '')
    {
        $elements = array();
        $userForm = new UserForm();

        // RAZ des options d'affichage du champ "username"
        // nécessaire car le champs est, par défaut, positionné à côté du bouton
        // d'auto-complétion dans l'IHM d'ajout et d'édition
        $userForm->get('username')->setOptions(array());

        foreach ($this->fieldsSearch as $field => $config) {
            if ($userForm->has($field)) {
                $element = $userForm->get($field);
                if ($config['autoComplete']) {
                    $element->setAttribute('data-ac-url', $url);
                    $element->setAttribute('data-ac-field', $field);
                }
                $elements[] = $element;
            }
        }

        return new SearchForm('user', $elements);
    }

    /**
     * Retourne le formulaire de création et d'édition d'un utilisateur
     *
     * @param int $userId
     * @return UserForm
     */
    public function getForm($userId = null)
    {
        $groupEntity = $this->getEntityInstance('group');
        $groups = $groupEntity->getSelectValues('name', 'fullname');

        $form = new UserForm();
        $form->get('groups')->setValueOptions($groups);

        $entity = $this->getEntityInstance('user');

        if ($userId === null) {
            $entity->getInputFilter()->get('password')->setRequired(true);
            $entity->getInputFilter()->get('password_confirm')->setRequired(true);
            $entity->getInputFilter()->remove('id_user');
            $form->remove('id_user');
        } else {
            $entity->load($userId);
            $entity->getInputFilter()->remove('username');
            $form->get('username')->setAttributes(array('disabled' => 'disabled'));
        }

        $form->bind($entity);

        $dataUser = $entity->getArrayCopy();
        $form->get('groups')->setValue(array_keys($dataUser['groups']));

        return $form;
    }

    /**
     * Retourne le formulaire de changement de mot de passe
     *
     * @return PasswordForm
     */
    public function getFormPassword()
    {
        $form = new PasswordForm();
        
        $form->get('username')
            ->setValue($this->identity->getUsername());

        return $form;
    }

    /**
     * Retourne le formulaire de réinitialisation de mot de passe
     *
     * @param string $username
     * @return PasswordForm
     */
    public function getFormPasswordReset($username)
    {
        $form = new PasswordForm();

        $form->get('username')->setValue($username);
        $form->remove('password');
        $form->getInputFilter()->remove('password');

        return $form;
    }

    /**
     * Retourne le formulaire de création et d'édition d'un utilisateur en lecture seule
     *
     * @param int $userId
     * @return UserForm
     */
    public function getFormReadOnly($userId)
    {
        $form = $this->getForm($userId);
                
        $form->remove('submit');
        $form->remove('reset');
        
        $elements = $form->getElements();
        foreach ($elements as $element) {
            $element->setAttributes(array('disabled' => 'disabled'));
        }

        return $form;
    }

    /**
     * Sauvegarde d'un utilisateur
     *
     * @param UserEntity $user
     * @throws DomainException
     */
    public function insert(UserEntity $user)
    {
        if ($user->hasUser()) {
            throw new DomainException('User already exists');
        }

        $user->save();
        $this->saveGroups($user);
    }

    /**
     * Met à jour les données d'un utilisateur
     *
     * @param UserEntity $user
     */
    public function update(UserEntity $user)
    {
        $user->save();
        $this->saveGroups($user);
    }

    /**
     * Supprime un utilisateur
     *
     * @param UserEntity $user
     * @param int $idUser
     */
    public function delete(UserEntity $user, $idUser)
    {
        $user->load($idUser);
        $groups = $user->getGroups();

        foreach ($groups as $group => $groupName) {
            $this->removeGroup($user, $group);
        }

        $user->delete();
    }

    /**
     * Met à jour les groupes de l'utilisateur
     *
     * @param UserEntity $user
     */
    public function saveGroups(UserEntity $user)
    {
        $newUserData = $user->getArrayCopy();
        $newGroups = $newUserData['groups'];
        $oldGroups = $user->getGroups();

        // Suppression des groupes décochés
        foreach ($oldGroups as $group => $groupName) {
            if (!in_array($group, $newGroups)) {
                $this->removeGroup($user, $group);
            }
        }
        // Ajout des nouveaux groupes cochés
        foreach ($newGroups as $group) {
            if (!array_key_exists($group, $oldGroups)) {
                $this->addGroup($user, $group);
            }
        }
    }

    /**
     * Supprime un groupe d'un utilisateur
     *
     * @param UserEntity $user
     * @param string $groupName
     */
    public function removeGroup(UserEntity $user, $groupName)
    {
        $idAclRole = $this->getIdGroup($groupName);
        $user->removeGroup($idAclRole);
    }

    /**
     * Ajoute un groupe à un utilisateur
     *
     * @param UserEntity $user
     * @param string $groupName
     */
    public function addGroup(UserEntity $user, $groupName)
    {
        $idAclRole = $this->getIdGroup($groupName);
        $user->addGroup($idAclRole);
    }

    /**
     * Retourne l'ID d'un groupe
     *
     * @param string $groupName
     * @return int
     */
    protected function getIdGroup($groupName)
    {
        $groupEntity = $this->getEntityInstance('group');
        $dataGroup = $groupEntity->getByName($groupName);

        return $dataGroup['id_acl_role'];
    }

    /**
     * Procède au changement de mot de passe d'un utilisateur
     * 
     * Le dernier paramètre permet de forcer sans avoir l'ancien mot de passe
     *
     * @param string $username
     * @param string $oldPassword
     * @param string $newPassword
     * @param boolean $change
     * @return boolean
     * @throws DomainException
     */
    public function changePassword($username, $oldPassword, $newPassword, $change = false)
    {
        $user = $this->getEntityInstance('user');        
        $user->loadByUsername($username);

        $data = $user->getArrayCopy();
        $password = md5($data['salt'] . $oldPassword);

        if ($data['password'] !== $password && $change === false) {
            throw new DomainException('The old password is incorrect');
        }

        $data['password'] = $newPassword;

        $user->exchangeArray($data);
        $user->save();
        $user->resetToken();

        return true;
    }

    /**
     * Envoi un email avec un lien de réinitialisation de mot de passe
     *
     * @param string $username
     * @return bool
     */
    public function forgotPassword($username)
    {
        $email = $this->getEmail($username);
        $token = $this->generateToken($username);

        return $this->sendEmailForgottenPassword($email, $username, $token);
    }

    /**
     * Retourne l'email d'un utilisateur
     *
     * @param string $username
     * @return string
     * @throws DomainException
     */
    public function getEmail($username)
    {
        $user = $this->getEntityInstance('user');
        $user->loadByUsername($username);

        $data = $user->getArrayCopy();
        
        $validator = new EmailAddress();
        
        if ($validator->isValid($data['username'])) {
            return $data['username'];
        }
        
        if ($validator->isValid($data['mail'])) {
            return $data['mail'];
        }
        
        throw new DomainException('Impossible to sent an email');
    }

    /**
     * Génère et retourne un token pour la RAZ du mot de passe d'un utilisateur
     *
     * @param string $username
     * @return string
     */
    public function generateToken($username)
    {
        $user = $this->getEntityInstance('user');        
        $user->loadByUsername($username);

        return $user->generateToken();
    }

    /**
     * Envoi de l'email de réinitialisation de mot de passe
     *
     * @param string $email
     * @param string $username
     * @param string $token
     * @return bool
     */
    protected function sendEmailForgottenPassword($email, $username, $token)
    {
        $resolver = new TemplateMapResolver();
        $resolver->setMap(array(
            'mailTemplate' => __DIR__ . '/../../views/_email/forgot.phtml',
        ));

        $viewModel = new ViewModel();
        $viewModel->setTemplate('mailTemplate')
            ->setVariables(array(
                'params' => array(
                    'username' => $username,
                    'token' => $token,
                ),
                'appName' => $this->appConfig['name'],
            )
        );

        $this->view->setResolver($resolver);
        $messageContent = $this->view->render($viewModel);

        $html = new MimePart($messageContent);
        $html->type = "text/html";

        $body = new MimeMessage();
        $body->setParts(array($html));

        $message = new MailMessage();
        $message
            ->setEncoding('UTF-8')
            ->addFrom(
                $this->appConfig['contact']['mail'],
                $this->translator->translate($this->appConfig['name'])
            )
            ->addTo($email)
            ->setSubject($this->translator->translate('Reset password'))
            ->setBody($body);

        $transport = new $this->transport();

        return $transport->send($message);
    }

    /**
     * Vérification de la validité d'un token de reset de mot de passe
     *
     * @param string $username
     * @param string $token
     * @return boolean
     */
    public function isValidToken($username, $token)
    {
        $user = $this->getEntityInstance('user');        
        $user->loadByUsername($username);

        $now = new DateTime();
        $data = $user->getArrayCopy();
        $tokenDate = new DateTime($data['token_date']);

        $interval = $tokenDate->getTimestamp() - $now->getTimestamp();

        if ($data['token'] === $token && $interval > 0) {
            return true;
        }

        return false;
    }

    /**
     * Retourne un tableeau des civilités possibles
     * 
     * @return array
     */
    public function getCivilities()
    {
        return array(
            0 => '-',
            1 => $this->translator->translate('Mr'),
            2 => $this->translator->translate('Mrs'),
            3 => $this->translator->translate('Ms'),
        );
    }

    /**
     * Retourne le résultat d'une recherche via l'auto-complétion
     *
     * @param int $entityKey
     * @param string $field
     * @param mixed $value
     * @return array
     * @throws DomainException
     */
    public function autoComplete($entityKey, $field, $value)
    {
        if (!isset($this->entityClassesName[$entityKey])) {
            throw new DomainException('Auto-complete refused');
        }

        $where[] = array(
            'field' => $field,
            'operator' => 'LIKE',
            'value' => $value
        );

        $entity = $this->getEntityInstance($entityKey);
        $result = $entity->fetchAll($where);

        $return = array();
        foreach ($result as $data) {
            $return[] = $data[$field];
        }

        return $return;
    }

}
