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

namespace Oft\Entity;

use ArrayAccess;
use Iterator;


class BaseEntity implements ArrayAccess, Iterator
{

    /**
     * Données
     *
     * @var array
     */
    protected $data = array();

    /**
     * Données
     *
     * @var array
     */
    protected $updatedFields = array();

    /**
     * Mode strict
     *
     * En mode strict, seules les valeurs des attributs précisés à la construction pourront être modifiées
     *
     * @var bool
     */
    protected $strict;

    /**
     * Position courante (Iterator)
     *
     * @var mixed
     */
    protected $currentPosition;

    /**
     * Liste des clefs (Iterator)
     *
     * @var array
     */
    protected $keys;


    /**
     * Constructeur
     *
     * @param array $data Données par défaut
     * @param type $strict Active ou pas le mode strict
     */
    public function __construct(array $data = array(), $strict = false)
    {
        $this->data = $data;
        $this->strict = $strict;
    }

    /**
     * @param array $newData
     * @return array Updated fields
     */
    public function exchangeArray($newData)
    {
        $currentData = $this->data;

        $dataKeys = array_merge(array_keys($this->data), array_keys($newData));

        foreach ($dataKeys as $key) {
            $existsInCurrentData = array_key_exists($key, $currentData);
            $existsInNewData = array_key_exists($key, $newData);

            if ($existsInCurrentData && $existsInNewData) {
                if ($this->data[$key] != $newData[$key]) {
                    $this->updatedFields[] = $key;
                    $this->data[$key] = $newData[$key];
                }
            } else if ($existsInCurrentData && !$existsInNewData) {
                $this->data[$key] = null;
            } else if (!$existsInCurrentData && $existsInNewData) {
                if (!$this->strict) {
                    $this->updatedFields[] = $key;
                    $this->data[$key] = $newData[$key];
                }
            }
        }

        return $currentData;
    }

    /**
     * @return array
     */
    public function getArrayCopy()
    {
        return $this->data;
    }

    /**
     * Ne retourne que les champs mis à jour
     * @return array
     */
    public function getUpdatedFields()
    {
        $result = array();
        foreach ($this->updatedFields as $field) {
            $result[$field] = $this->data[$field];
        }

        return $result;
    }

    public function setUpdatedFields(array $updatedFields = array())
    {
        $this->updatedFields = $updatedFields;
    }

    public function offsetExists($name)
    {
        return isset($this->data[$name]);
    }

    public function offsetGet($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        return null;
    }

    public function offsetSet($name, $value)
    {
        if ($this->strict && !array_key_exists($name, $this->data)) {
            return; // New attributes can not be added in BaseEntity in strict mode
        }

        if (!array_key_exists($name, $this->data) || $value !== $this->data[$name]) {
            $this->updatedFields[] = $name;
        }
        $this->data[$name] = $value;
    }

    public function offsetUnset($name)
    {
        $this->data[$name] = null;
    }

    public function __set($name, $value)
    {
        if ($this->strict && !array_key_exists($name, $this->data)) {
            return; // New attributes can not be added in BaseEntity in strict mode
        }

        if (!array_key_exists($name, $this->data) || $value !== $this->data[$name]) {
            $this->updatedFields[] = $name;
        }
        $this->data[$name] = $value;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        return null;
    }

    public function __unset($name)
    {
        if (array_key_exists($name, $this->data)) {
            $this->data[$name] = null;
            $this->updatedFields[] = $name;
        }
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    public function current()
    {
        return $this->data[$this->keys[$this->currentPosition]];
    }

    public function key()
    {
        return $this->keys[$this->currentPosition];
    }

    public function next()
    {
        $this->currentPosition ++;
    }

    public function rewind()
    {
        $this->keys = array_keys($this->data);
        $this->currentPosition = 0;
    }

    public function valid()
    {
        return array_key_exists($this->currentPosition, $this->keys);
    }

}
