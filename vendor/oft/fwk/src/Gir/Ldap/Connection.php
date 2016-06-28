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

namespace Oft\Gir\Ldap;

use RuntimeException;

/**
 * Gestion de l'ouverture et de la fermeture de la connexion à l'annuaire interne via le protocole LDAP
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class Connection
{

    /**
     * Options de connexion
     *
     * @var array
     */
    protected $options;

    /**
     * Ressource de connexion LDAP
     * 
     * @var resource
     */
    protected $resource;

    /**
     * Définition des options
     *
     * @param array $options
     * @return self
     */
    public function setOptions(array $options)
    {
        $permittedOptions = array(
            'host' => null,
            'port' => 0,
            'useSsl' => false,
            'username' => null,
            'password' => null,
            'baseDn' => null,
        );

        foreach ($permittedOptions as $key => $val) {
            if (array_key_exists($key, $options)) {
                $val = $options[$key];
                unset($options[$key]);

                $permittedOptions[$key] = trim($val);
            }
        }

        $this->options = $permittedOptions;

        return $this;
    }

    /**
     * Retourne les options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Connexion LDAP
     *
     * @throws RuntimeException
     * @return resource
     */
    public function connect()
    {
        $host = $this->options['host'];
        $port = (int) $this->options['port'];
        $useSsl = (bool) $this->options['useSsl'];

        if (!$host) {
            throw new RuntimeException('A LDAP host must be defined');
        }

        if ($useSsl) {
            $connectString = 'ldaps://' . $host . ':' . $port;

            $resource = ldap_connect($connectString);
        } else {
            $resource = ldap_connect($host, $port);
        }

        if (is_resource($resource)) {
            $this->resource = $resource;

            ldap_set_option($resource, LDAP_OPT_PROTOCOL_VERSION, 3);
        } else {
            throw new RuntimeException('LDAP connection failed');
        }
        
        return $resource;
    }

    /**
     * Retourne la ressource Ldap
     *
     * @return resource
     */
    public function getResource()
    {
        if (!is_resource($this->resource)) {
            $this->bind();
        }

        return $this->resource;
    }

    /**
     * Déconnexion
     * 
     * @return bool
     */
    public function disconnect()
    {
        if (!is_resource($this->resource)) {
            return true;
        }
        
        return ldap_close($this->resource);
    }

    /**
     * Connexion & bind
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function bind()
    {
        $username = $this->options['username'];
        $password = $this->options['password'];

        if (!is_resource($this->resource)) {
            $this->connect();
        }

        $bind = ldap_bind($this->resource, $username, $password);

        if (!$bind) {
            throw new RuntimeException('LDAP bind failed');
        }

        return $bind;
    }

}
