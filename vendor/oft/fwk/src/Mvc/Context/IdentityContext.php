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

namespace Oft\Mvc\Context;

use Oft\Auth\Identity;
use Oft\Http\SessionInterface;

class IdentityContext
{
    /**
     * Identité
     *
     * @var Identity
     */
    protected $identity;

    /**
     *
     * @var SessionInterface
     */
    protected $session;

    /**
     * Nombre de secondes jusqu'à expiration de l'identité en session
     *
     * @var int
     */
    protected $expiration;

    public function __construct(SessionInterface $session, $expiration, Identity $identity = null)
    {
        $this->session = $session;
        $this->expiration = $expiration;
        $this->identity = $identity;

    }

    /**
     * Retourne l'identité courante
     *
     * Une identité vierge sera créée si non-définie
     *
     * @return Identity
     */
    public function get()
    {
        if ($this->identity === null) {
            $container = $this->session->getContainer(__CLASS__);

            if (isset($container->expiration) && $this->getTime() <= $container->expiration && isset($container->identity)) {
                $container->expiration = $this->getTime() + $this->expiration;

                $this->identity = $container->identity;
            }

            if ($this->identity === null) {
                $this->identity = new Identity(array());
            }
        }

        return $this->identity;
    }

    /**
     * Définit l'identité courante
     *
     * @param Identity $identity
     * @return self
     */
    public function set(Identity $identity)
    {
        $container = $this->session->getContainer(__CLASS__);

        $container->expiration = $this->getTime() + $this->expiration;
        $container->identity = $identity;

        $this->identity = $identity;
    }

    /**
     * Supprime l'identité courante
     *
     * @return void
     */
    public function drop()
    {
        $this->identity = null;
        $this->session->dropContainer(__CLASS__);
    }

    /**
     * Récupère l'heure d'exécution du script
     *
     * @return int
     */
    protected function getTime()
    {
        if (isset($_SERVER['REQUEST_TIME']) && is_int($_SERVER['REQUEST_TIME'])) {
            $time = $_SERVER['REQUEST_TIME'];
        } else {
            $time = time();
        }

        return $time;
    }
}
