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

namespace Oft\Form\Element;

use Zend\Form\Element;
use Zend\Form\ElementPrepareAwareInterface;
use Zend\Form\FormInterface;
use Zend\InputFilter\InputProviderInterface;
use Oft\Validator\Csrf as CsrfValidator;

class Csrf extends Element implements InputProviderInterface, ElementPrepareAwareInterface
{
    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes = array(
        'type' => 'hidden',
    );

    /**
     * @var array
     */
    protected $csrfValidatorOptions = array();

    /**
     * @var CsrfValidator
     */
    protected $csrfValidator;

    /**
     * Accepted options for Csrf:
     * - csrf_options: an array used in the Csrf
     *
     * @param array|\Traversable $options
     * @return Csrf
     */
    public function setOptions($options)
    {
        parent::setOptions($options);

        if (isset($options['csrf_options'])) {
            $this->setCsrfValidatorOptions($options['csrf_options']);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getCsrfValidatorOptions()
    {
        return $this->csrfValidatorOptions;
    }

    /**
     * @param  array $options
     * @return Csrf
     */
    public function setCsrfValidatorOptions(array $options)
    {
        $this->csrfValidatorOptions = $options;

        return $this;
    }

    /**
     * Get CSRF validator
     *
     * @return CsrfValidator
     */
    public function getCsrfValidator()
    {
        if (null === $this->csrfValidator) {
            $csrfOptions = $this->getCsrfValidatorOptions();
            $csrfOptions = array_merge($csrfOptions, array('name' => $this->getName()));
            $this->setCsrfValidator(new CsrfValidator($csrfOptions));
        }

        return $this->csrfValidator;
    }

    /**
     * @param CsrfValidator $validator
     * @return $this
     */
    public function setCsrfValidator(CsrfValidator $validator)
    {
        $this->csrfValidator = $validator;

        return $this;
    }

    /**
     * Retrieve value
     *
     * Retrieves the hash from the validator
     *
     * @return string
     */
    public function getValue()
    {
        $validator = $this->getCsrfValidator();

        return $validator->getHash();
    }

    /**
     * Override: get attributes
     *
     * Seeds 'value' attribute with validator hash
     *
     * @return array
     */
    public function getAttributes()
    {
        $attributes = parent::getAttributes();
        $validator  = $this->getCsrfValidator();
        $attributes['value'] = $validator->getHash();

        return $attributes;
    }

    /**
     * Provide default input rules for this element
     *
     * Attaches the captcha as a validator.
     *
     * @return array
     */
    public function getInputSpecification()
    {
        return array(
            'name' => $this->getName(),
            'required' => true,
            'filters' => array(
                array('name' => 'Zend\Filter\StringTrim'),
            ),
            'validators' => array(
                $this->getCsrfValidator(),
            ),
        );
    }

    /**
     * @param FormInterface $form
     * @return $this
     */
    public function prepareElement(FormInterface $form)
    {
        $this->getCsrfValidator()->getHash(true);

        return $this;
    }
}
