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

namespace Oft\Acl;

use Oft\Acl\Acl as Oft_Acl;
use Oft\Acl\Adapter\AdapterInterface;
use Oft\Auth\Identity;
use Oft\Mvc\Application;
use Oft\Service\CachedFactoryAbstract;
use Oft\Service\ServiceLocatorInterface;
use Zend\Permissions\Acl\Acl as ZendAcl;
use Zend\Permissions\Acl\Resource\GenericResource;
use Zend\Permissions\Acl\Role\GenericRole;

class AclFactory extends CachedFactoryAbstract
{

    /**
     * Instancie et configure l'objet de gestion des ACL
     *
     * @param Application $app
     * @return ZendAcl
     */
    public function doCreate(ServiceLocatorInterface $app)
    {
        /* @var $storage AdapterInterface */
        $storage = $app->get('AclStore');
        $router = $app->get('Router');

        // ACL
        $acl = new Oft_Acl($router->getRoutes(), $app->config['acl']['whitelist']);

        // Rôles génériques
        $acl->addRole(new GenericRole(Identity::ADMIN_GROUP));
        $acl->addRole(new GenericRole(Identity::GUEST_GROUP));

        // Administrator
        $acl->allow(Identity::ADMIN_GROUP);

        // Rôles configurés
        $roles = $storage->getRoles();
        foreach ($roles as $role) {
            if ($acl->hasRole($role)) {
                continue;
            }
            $acl->addRole(new GenericRole($role));
        }

        // Allow
        $allowed = $storage->getAllowed();
        foreach ($allowed as $allow) {
            foreach ($allow['resources'] as $resource) {
                if (!$acl->hasResource($resource)) {
                    $acl->addResource(new GenericResource($resource));
                }

                foreach ($allow['roles'] as $role) {
                    $acl->allow($role, $resource);
                }
            }
        }
        
        return $acl;
    }

}
