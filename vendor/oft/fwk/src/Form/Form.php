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

namespace Oft\Form;

use Oft\Form\Element\Csrf as CsrfElement;
use Oft\Form\Element\DateTime;
use Oft\Form\Element\Float;
use Oft\Form\Hydrator\DateTimeStrategy;
use Oft\Form\Hydrator\FloatStrategy;
use Zend\Form\ElementInterface;
use Zend\Form\Form as ZendForm;
use Zend\InputFilter\InputFilter;

class Form extends ZendForm
{

    /**
     * Initialisation
     *
     * @param string $name Nom du formulaire
     * @param array $options Options du formulaire
     * @return self
     */
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, $options);

        if (!isset($this->options['csrf_name'])) {
            if ($name === null) {
                $this->options['csrf_name'] = 'csrf';
            } else {
                $this->options['csrf_name'] = $name . '_csrf';
            }
        }

        $csrf = new CsrfElement($this->options['csrf_name']);
        
        if (isset($this->options['csrf_options'])) {
            $csrfOption['csrf_options'] = $this->options['csrf_options'];
            $csrf->setOptions($csrfOption);
        }
        
        $this->add($csrf);

        $this->setAttribute('role', 'form');

        $this->init();
    }

    public function init()
    {

    }

    public function add($elementOrFieldset, array $flags = array())
    {
        parent::add($elementOrFieldset, $flags);

        if (!isset($this->filter)) {
            $this->filter = new InputFilter();
        }

        // Handle array defined input_filters
        if (is_array($elementOrFieldset)) {
            if (array_key_exists('name', $flags) && strlen($flags['name'])) {
                $name = $flags['name'];
            } else {
                $name = $elementOrFieldset['name'];
            }

            if (isset($elementOrFieldset['input_filter'])) {
                // Preservation du comportement par défaut : on ajoute pas de 
                // filtres/validateurs si ils ont été définis par l'élément lui même
                if (!$this->filter->has($name)) {
                    $this->filter->add($elementOrFieldset['input_filter'], $name);
                }
            }
        } else if ($elementOrFieldset instanceof ElementInterface) {
            $name = $elementOrFieldset->getName();
        }
        
        $elm = $this->get($name);

        // Handle DateTimeStrategy auto insertion
        if ($elm instanceof DateTime) {
            $this->getHydrator()->addStrategy(
                $elm->getName(),
                new DateTimeStrategy($elm)
            );
        }

        // Handle FloatStrategy auto insertion
        if ($elm instanceof Float) {
            $this->getHydrator()->addStrategy(
                $elm->getName(),
                new FloatStrategy()
            );
        }

        return $this;
    }

    public function remove($name)
    {
        parent::remove($name);

        $this->filter->remove($name);

        return $this;
    }

    public function isValid()
    {
        if ($this->hasValidated) {
            return $this->isValid;
        }

        $csrfName = $this->options['csrf_name'];
        $csrfValue = isset($this->data[$csrfName]) ? $this->data[$csrfName] : null;

        $csrfValidator = $this->get($csrfName)->getCsrfValidator();

        if ($csrfValidator->isValid($csrfValue)) {
            $this->getInputFilter()->remove($csrfName);
        } else {
            $this->get($csrfName)->setMessages($csrfValidator->getMessages());
            $this->hasValidated = true;
            
            return false;
        }

        return parent::isValid();
    }

}
