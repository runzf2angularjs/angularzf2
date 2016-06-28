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

namespace Oft\Service;

use Oft\Mvc\Application;

class ServiceManager implements ServiceLocatorInterface
{

    /** @var Application */
    protected $app;

    /** @var array */
    protected $instances = array();

    /** @var array */
    protected $definitions = array();

    /** @var array */
    protected $interfaces = array();

    /** @var array */
    protected $currentService = array();

    public function __construct(array $definitions = array(), array $interfaces = array())
    {
        $this->addServicesDefinitions($definitions);
        $this->addServicesInterfaces($interfaces);
    }

    public function addServicesDefinitions(array $definitions)
    {
        foreach ($definitions as $name => $definition) {
            if (is_string($definition)) {
                $this->addServiceDefinition($name, $definition);
            } else {
                throw new \RuntimeException("Invalid service definition (should be a string or an array)");
            }
        }

        return $this;
    }

    public function addServicesInterfaces(array $interfaces)
    {
        foreach ($interfaces as $name => $interface) {
            if (is_string($interface)) {
                $this->addServiceInterface($name, $interface);
            } else {
                throw new \RuntimeException("Invalid service interface (should be a string or an array)");
            }
        }

        return $this;
    }

    public function addServiceDefinition($cName, $class, $interface = null)
    {
        if (isset($this->definitions[$cName])) {
            throw new \RuntimeException('That definition already exists');
        }

        if ($interface !== null) {
            $this->addServiceInterface($cName, $interface);
        }

        $this->definitions[$cName] = $class;

        return $this;
    }

    public function addServiceInterface($cName, $interface)
    {
        if (isset($this->interfaces[$cName])) {
            throw new \RuntimeException('That service\'s interface has already been defined');
        }

        $this->interfaces[$cName] = $interface;

        return $this;
    }

    public function setService($cName, $instance, $overwrite = false)
    {
        if ($instance === null) {
            throw new \RuntimeException("Service instance is null");
        }

        if (!$overwrite && isset($this->instances[$cName])) {
            throw new \RuntimeException('An instance of the same name already exists');
        }

        if (isset($this->interfaces[$cName])) {
            if (!$instance instanceof $this->interfaces[$cName]) {
                throw new \RuntimeException("The service instance does not comply to the interface specified");
            }
        }

        $this->instances[$cName] = $instance;

        return $this;
    }

    public function get($cName)
    {
        if (isset($this->instances[$cName])) {
            return $this->instances[$cName];
        }

        if (!isset($this->definitions[$cName])) {
            throw new \RuntimeException("No service defined with name '" . $cName . "'");
        }

        if (!class_exists($this->definitions[$cName])) {
            throw new \RuntimeException("The service class '" . $this->definitions[$cName] . "' does not exists");
        }

        if (isset($this->currentService[$cName])) {
            throw new \RuntimeException('Cyclic dependency detected');
        }
        $this->currentService[$cName] = true;

        try {
            // Try to create an instance of the service
            $instance = new $this->definitions[$cName]($this);

            // If it is a factory we will call 'create' on it
            if ($instance instanceof FactoryInterface) {
                $serviceInstance = $instance->create($this);
            } else {
                $serviceInstance = $instance; // Invokable
            }

            $this->setService($cName, $serviceInstance);
        } catch (\Exception $e) {
            unset($this->currentService[$cName]);
            throw $e;
        }

        unset($this->currentService[$cName]);

        return $this->instances[$cName];
    }

    public function has($cName)
    {
        if (isset($this->instances[$cName])) {
            return true;
        }

        if (isset($this->definitions[$cName])) {
            return true;
        }

        return false;
    }

}
