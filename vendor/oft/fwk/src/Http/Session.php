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

namespace Oft\Http;

use Symfony\Component\HttpFoundation\Session\SessionInterface as SfSessionInterface;

class Session implements SessionInterface
{

    /**
     * @var SfSessionInterface
     */
    protected $session;

    /**
     * @param SfSessionInterface $session
     */
    public function __construct(SfSessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @return SfSessionInterface
     */
    public function getSessionObject()
    {
        return $this->session;
    }

    /**
     * Savoir si la session est démarrée
     *
     * @return bool
     */
    public function isStarted()
    {
        return $this->session->isStarted;
    }

    /**
     * Démarre la session
     *
     * @return $this
     */
    public function start()
    {
        $this->session->start();

        return $this;
    }

    /**
     * Détruit la session
     *
     * @return $this
     */
    public function destroy()
    {
        $this->session->invalidate();

        return $this;
    }

    /**
     * Regénère l'id de session
     *
     * @return $this
     */
    public function regenerateId()
    {
        $this->session->migrate(false);

        return $this;
    }

    /**
     * Récupère un container
     *
     * Enregistre le container en session s'il n'existe pas
     *
     * @param $name
     * @param null $expiration
     * @return mixed
     */
    public function getContainer($name, $expiration = null)
    {
        if (!$this->session->has($name)) {
            $this->session->set($name, new \stdClass);
        }

        return $this->session->get($name);
    }

    /**
     * Supprime un container
     *
     * @param $name
     * @return $this
     */
    public function dropContainer($name)
    {
        $this->session->remove($name);

        return $this;
    }


}
