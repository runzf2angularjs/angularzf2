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

namespace Oft\Acl\Adapter;

use Doctrine\DBAL\Driver\Connection as ConnectionInterface;
use Oft\Service\ServiceLocatorInterface;

class Db implements AdapterInterface
{

    /**
     * Connexion à la base de données
     *
     * @var ConnectionInterface
     */
    protected $db;

    /**
     * Tableau référentiel des permissions
     *
     * @var array
     */
    protected $permissions = array(
        'roles' => array(),
        'allow' => array(),
    );

    /**
     * Initialisation
     *
     * @param ServiceLocatorInterface $app Application
     */
    public function __construct(ServiceLocatorInterface $app)
    {
        $this->db = $app->db;
        $this->init();
    }

    /**
     * Initialisation
     *
     * Alimente le tableau référentiel des permissions
     * à partir de la base de données
     *
     * @todo : Utiliser Oft\Auth\Identity::getGroups
     *
     * @return void
     */
    protected function init()
    {
        $queryBuilder = $this->db->createQueryBuilder()
            ->select('ro.name as role', 're.name as resource')
            ->from('oft_acl_role_resource', 'p')
            ->leftJoin(
                'p',
                'oft_acl_roles',
                'ro',
                'p.id_acl_role = ro.id_acl_role'
            )
            ->leftJoin(
                'p',
                'oft_acl_resources',
                're',
                'p.id_acl_resource = re.id_acl_resource'
            );

        $rules = $queryBuilder->execute();

        foreach ($rules as $rule) {
            // Add role
            if (!in_array($rule['role'], $this->permissions['roles'])) {
                $this->permissions['roles'][] = $rule['role'];
            }
            // Add allowed rule for this role
            if (!array_key_exists($rule['role'], $this->permissions['allow'])) {
                $this->permissions['allow'][$rule['role']] = array(
                    'resources' => array(),
                    'roles' => array($rule['role']),
                );
            }
            $this->permissions['allow'][$rule['role']]['resources'][] = $rule['resource'];
        }
    }

    /**
     * Retourne les groupes utilisateurs
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->permissions['roles'];
    }

    /**
     * Retourne les règles d'ouverture ressource(s) / groupe(s)
     *
     * @return array
     */
    public function getAllowed()
    {
        return $this->permissions['allow'];
    }

}
