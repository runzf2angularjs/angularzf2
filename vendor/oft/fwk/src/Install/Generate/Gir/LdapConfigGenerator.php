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

namespace Oft\Install\Generate\Gir;

use InvalidArgumentException;
use Oft\Install\Generate\File;
use Oft\Install\Generate\GeneratorAbstract;

class LdapConfigGenerator extends GeneratorAbstract
{

    /**
     * GIR : active/inactive
     *
     * @var boolean
     */
    public $active;

    /**
     * Utilisation de SSL
     *
     * @var boolean
     */
    public $useSsl;

    /**
     * baseDn
     *
     * @var string
     */
    public $baseDn;

    /**
     * Host
     *
     * @var string
     */
    public $host;
    
    /**
     * Username
     *
     * @var string
     */
    public $username;

    /**
     * Password
     *
     * @var string
     */
    public $password;

    /**
     * Port
     *
     * @var string
     */
    public $port;

    /**
     * Génération du code
     */
    public function generate()
    {
        $template = 'ldap-config';

        $destination = APP_ROOT . '/application/config/config.gir.php';
        
        if (!in_array($this->useSsl, array('0', '1', 0, 1), true)) {
            throw new InvalidArgumentException("use-ssl doit être 0 ou 1");
        }

        if ($this->useSsl) {
            $this->useSsl = 'true';
        } else {
            $this->useSsl = 'false';
        }
        
        $content = $this->render($template, array(
            'active' => $this->active,

            'useSsl' => $this->useSsl,
            'baseDn' => $this->baseDn,
            'host' => $this->host,
            'username' => $this->username,
            'password' => $this->password,
            'port' => $this->port,
        ));

        $this->addFile(new File($destination, $content));
    }

}
