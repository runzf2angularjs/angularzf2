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

namespace Oft\Validator;

use Oft\Http\SessionInterface;
use Oft\Util\Functions;
use Zend\Math\Rand;
use Zend\Validator\AbstractValidator;

class Csrf extends AbstractValidator
{
    /**
     * Error codes
     * @const string
     */
    const NOT_SAME = 'notSame';

    /**
     * Error messages
     * @var array
     */
    protected $messageTemplates = array(
        self::NOT_SAME => "The form submitted did not originate from the expected site",
    );

    /**
     * Actual hash used.
     *
     * @var mixed
     */
    protected $hash;

    /**
     * Name of CSRF element (used to create non-colliding hashes)
     *
     * @var string
     */
    protected $name = 'csrf';

    /**
     * Salt for CSRF token
     * @var string
     */
    protected $salt = 'salt';

    /**
     * Container of session
     *
     * @var mixed
     */
    protected $container;
    
    /**
     *
     * @var array 
     */
    protected $options = array(
        'timeout' => 300,     // TTL for CSRF token  
    );

    /**
     * Does the provided token match the one generated?
     *
     * @param  string $value
     * @param  mixed $context
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        $this->setValue((string) $value);

        $tokenId = $this->getTokenIdFromHash($value);
        $hash = $this->getValidationToken($tokenId);

        if ($this->getTokenFromHash($value) !== $this->getTokenFromHash($hash)) {
            $this->error(self::NOT_SAME);
            return false;
        }
        
        // Remove used token
        $container = $this->getContainer();
        unset($container->tokenList[$tokenId]);

        return true;
    }

    /**
     * Set CSRF name
     *
     * @param  string $name
     * @return Csrf
     */
    public function setName($name)
    {
        $this->name = (string) $name;
        return $this;
    }

    /**
     * Get CSRF name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @todo
     *
     * @return SessionInterface
     */
    protected function getSession()
    {
        $app = Functions::getApp();

        return $app->http->session;
    }

    /**
     * Get session container
     *
     * Instantiate session container if none currently exists
     *
     * @return mixed
     */
    public function getContainer()
    {
        $session = $this->getSession();
        $timeout = $this->getTimeout();
        $time = $this->getTime();

        $container = $session->getContainer($this->getContainerName());

        // Décomposition de la logique pour la lisibilité
        if (! isset($container->expiration)) {
            // Container créé
            $container->expiration = $time + $timeout;
        } elseif ($time >= $container->expiration){
            // Timeout dépassé, reset container
            $session->dropContainer($this->getContainerName());
            $container = $session->getContainer($this->getContainerName());
            $container->expiration = $time + $timeout;
        } else {
            // Container existant et timeout non dépassé
            $container->expiration = $time + $timeout;
        }

        return $container;
    }

    /**
     * Salt for CSRF token
     *
     * @param  string $salt
     * @return Csrf
     */
    public function setSalt($salt)
    {
        $this->salt = (string) $salt;

        return $this;
    }

    /**
     * Retrieve salt for CSRF token
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Retrieve CSRF token
     *
     * If no CSRF token currently exists, or should be regenerated,
     * generates one.
     *
     * @param  bool $regenerate    default false
     * @return string
     */
    public function getHash($regenerate = false)
    {
        if ((null === $this->hash) || $regenerate) {
            $this->generateHash();
        }

        return $this->hash;
    }

    /**
     * Get session namespace for CSRF token
     *
     * Generates a session namespace based on salt, element name, and class.
     *
     * @return string
     */
    public function getContainerName()
    {
        return str_replace('\\', '_', __CLASS__) . '_'
            . $this->getSalt() . '_'
            . strtr($this->getName(), array('[' => '_', ']' => ''));
    }

    /**
     * Set timeout for CSRF session token
     *
     * @param  int|null $ttl
     * @return Csrf
     */
    public function setTimeout($ttl)
    {
        $this->options['timeout'] = ($ttl !== null) ? (int) $ttl : null;
        
        return $this;
    }

    /**
     * Get CSRF session token timeout
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->getOption('timeout');
    }

    /**
     * Initialize CSRF token in session
     *
     * @return void
     */
    protected function initCsrfToken()
    {
        $container = $this->getContainer();
        $hash = $this->getHash();
        $token = $this->getTokenFromHash($hash);
        $tokenId = $this->getTokenIdFromHash($hash);

        if (! isset($container->tokenList)) {
            $container->tokenList = array();
        }
        $container->tokenList[$tokenId] = $token;
    }

    /**
     * Generate CSRF token
     *
     * Generates CSRF token and stores both in {@link $hash} and element
     * value.
     *
     * @return void
     */
    protected function generateHash()
    {
        $token = md5($this->getSalt() . Rand::getBytes(32) .  $this->getName());

        $this->hash = $this->formatHash($token, $this->generateTokenId());

        $this->setValue($this->hash);
        $this->initCsrfToken();
    }

    /**
     * @return string
     */
    protected function generateTokenId()
    {
        return md5(Rand::getBytes(32));
    }

    /**
     * Get validation token
     *
     * Retrieve token from session, if it exists.
     *
     * @param string $tokenId
     * @return null|string
     */
    protected function getValidationToken($tokenId)
    {
        $container = $this->getContainer();

        if ($tokenId && isset($container->tokenList[$tokenId])) {
            return $this->formatHash($container->tokenList[$tokenId], $tokenId);
        }

        return null;
    }

    /**
     * @param $token
     * @param $tokenId
     * @return string
     */
    protected function formatHash($token, $tokenId)
    {
        return sprintf('%s-%s', $token, $tokenId);
    }

    /**
     * @param $hash
     * @return string
     */
    protected function getTokenFromHash($hash)
    {
        $data = explode('-', $hash);
        return $data[0] ?: null;
    }

    /**
     * @param $hash
     * @return string
     */
    protected function getTokenIdFromHash($hash)
    {
        $data = explode('-', $hash);

        if (! isset($data[1])) {
            return null;
        }

        return $data[1];
    }

    /**
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
