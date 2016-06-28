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

use Oft\Date\DateFormatter;
use Oft\Util\Functions;
use Oft\Validator\DateTime as ValidatorDateTime;
use Zend\Form\Element;
use Zend\InputFilter\InputProviderInterface;

/**
 * Elément de formulaire de type "date-heure"
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class DateTime extends Element implements InputProviderInterface
{
    
    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes = array(
        'type' => 'text',
        'dateFormat' => 'short',
        'timeFormat' => 'medium',
        'dateSqlFormat' => 'sql',
        'timeSqlFormat' => 'sql',
    );
    
    /**
     * @var ValidatorDateTime 
     */
    protected $validator;

    /**
     * @var DateFormatter
     */
    protected $dateFormatter;
    
    /**
     * @param null|int|string $name
     * @param array $options
     */
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, $options);

        if ($this->getOption('dateFormatter') instanceof DateFormatter) {
            $this->dateFormatter = $this->getOption('dateFormatter');
        } else {
            $this->dateFormatter = Functions::getApp()->get('DateFormatter');
        }
    }
    
    /**
     * Gère les attributs
     * 
     * {@inheritdoc}
     */
    public function setOptions($options)
    {
        foreach ($options as $option => $value) {
            if (array_key_exists($option, $this->attributes)) {
                $this->setAttribute($option, $value);
                unset($options[$option]);
            }
        }
        
        return parent::setOptions($options);
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
            'filters' => array(
                array('name' => 'Zend\Filter\StringTrim'),
                array('name' => 'Zend\Filter\StripTags'),
            ),
            'validators' => array(
                $this->getDatetimeValidator(),
            ),
        );
    }
    
    /**
     * Récupération du validateur
     * 
     * @return ValidatorDateTime
     */
    protected function getDatetimeValidator()
    {
        if ($this->validator === null) {
            $validator = new ValidatorDateTime(
                $this->dateFormatter,
                $this->attributes['dateFormat'], 
                $this->attributes['dateSqlFormat'],
                $this->attributes['timeFormat'],
                $this->attributes['timeSqlFormat']
            );
            
            $this->validator = $validator;
        }
        
        return $this->validator;
    }
    
    /**
     * Récupération du formateur de dates
     * 
     * @return DateFormatter
     */
    public function getDateFormatter()
    {
        return $this->dateFormatter;
    }
    
}
