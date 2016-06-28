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

namespace Oft\View\Helper;

use Oft\Form\Element\Csrf as OftCsrf;
use Zend\Form\Element\Csrf as ZendCsrf;
use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\Form\View\Helper\Form as FormHelper;
use Zend\I18n\View\Helper\AbstractTranslatorHelper;
use Zend\InputFilter\InputFilterInterface;

class SmartForm extends AbstractTranslatorHelper
{

    /**
     * Options par défaut pour le rendu du formulaire
     *
     * @var array
     */
    protected $defaultOptions = array(
        // Attributs du tag <form>
        'attr_role_form' => 'form',
        'attr_class_form' => 'form-horizontal',
    );

    /**
     * Options pour le rendu du formulaire
     *
     * @var array
     */
    protected $options = null;

    /**
     * Règles de filtrage des champs du formulaire
     *
     * @var null|InputFilterInterface
     */
    protected $formInputFilter = null;

    /**
     * Liste des éléments traités et affichés
     *
     * @var array
     */
    protected $displayedElements = array();

    /**
     * Flag : ligne ouverte/fermée pour le rendu des éléments
     *
     * @var bool
     */
    protected $openedRow = false;

    /**
     *
     * @var Form
     */
    protected $form = null;
    
    /**
     *  
     * @var array 
     */
    protected $fieldsetPath = array();
    
    /**
     * Retourne le code HTML pour l'affichage simplifié d'un formulaire
     *
     * @param Form $form Formulaire
     * @param array $options Options pour le rendu du formulaire
     * @return string
     */
    public function __invoke(Form $form, array $options = array())
    {
        $this->form = $form;
        $this->reset();
        $this->mergeOptions($options);
        $this->setFormInputFilter($form->getInputFilter());

        $form->prepare();

        // Ouverture du form
        $html = $this->getFormOpenTag($form, $this->view->form()) . "\n";

        // Eléments + Fieldset
        $elements = $form->getIterator()->toArray();
        if (count($elements)) {
            $html .= $this->renderElements($elements);
        }

        // Fermeture du form
        $html .= $this->getFormCloseTag() . "\n";
        
        return $html;
    }
    
    /**
     * Rendu d'un fieldset
     * 
     * @param Fieldset $fieldset
     * @return string
     */
    public function renderFieldset(Fieldset $fieldset) 
    {
        $html = $this->getFieldsetOpenTag($fieldset, $this->view->form()) . "\n";
        
        $this->fieldsetPath[] = $fieldset->getName();
        
        $legend = $fieldset->getLabel();
        if ($legend) {
            $html .= '<legend>' . $legend . '</legend>' . "\n";
        }
        
        // Eléments + Fieldsets
        $elements = $fieldset->getIterator()->toArray();
        if (count($elements)) {
            $html .= $this->renderElements($elements);
        }
        
        $html .= $this->getFieldsetCloseTag();

        array_pop($this->fieldsetPath);
        
        return $html;
    }

    /**
     * Remise à zéro
     *
     * @return void
     */
    public function reset()
    {
        // RAZ Options
        $this->options = $this->defaultOptions;
        
        $this->openedRow = false;
    }

    /**
     * Ecrase les options par défaut par celles fournies
     *
     * @param array $options
     * @return void
     */
    public function mergeOptions(array $options = array())
    {
        $this->options = array_merge(
            $this->defaultOptions, $options
        );
    }

    /**
     * Retourne les règles de filtrage sur les champs du formulaire
     *
     * @return null|InputFilterInterface
     */
    public function getFormInputFilter()
    {
        return $this->formInputFilter;
    }

    /**
     * Définit les règles de filtrage sur les champs du formulaire
     *
     * @param $formInputFilter InputFilterInterface
     * @return void
     */
    public function setFormInputFilter(InputFilterInterface $formInputFilter = null)
    {
        $this->formInputFilter = $formInputFilter;
    }

    /**
     * Retourne le tag d'ouverture du formulaire donné
     *
     * @param Form $form
     * @param FormHelper $formHelper
     * @return string
     */
    public function getFormOpenTag(Form $form, FormHelper $formHelper)
    {
        // Attributs
        $defaultAttributes = array(
            'action' => '',
            'method' => 'get',
        );

        // Attributs du formulaire - Définition de 'id' si nécessaire
        $formAttributes = $form->getAttributes();
        if (!array_key_exists('id', $formAttributes) && array_key_exists('name', $formAttributes)) {
            $formAttributes['id'] = $formAttributes['name'];
        }

        // Merge
        $attributes = array_merge($defaultAttributes, $formAttributes);

        // Attribut 'role', selon les options
        $attributes['role'] = $this->options['attr_role_form'];

        // Attribut 'class', selon les options
        $attributes['class'] = $this->options['attr_class_form'];
        

        return sprintf('<form %s>', $formHelper->createAttributesString($attributes));
    }

    /**
     * Retourne le tag de fermeture du formulaire
     *
     * @return string
     */
    public function getFormCloseTag()
    {
        return '</form>';
    }

    /**
     * Retourne TRUE si l'élément donné est obligatoire, FALSE sinon
     *
     * @param string $elementName
     * @param Fieldset $fieldset
     * @return bool
     */
    public function isElementRequired(Element $element)
    {
        $inputFilter = $this->getFormInputFilter();
        
        if (! empty($this->fieldsetPath)) {
            $currentPathFieldset = $this->fieldsetPath[max(array_keys($this->fieldsetPath))];
            
            $fieldsetPathArray = $this->getFieldPathInArray($currentPathFieldset);
            foreach($fieldsetPathArray as $fieldsetName) {
                if ($inputFilter->has($fieldsetName)) {
                    $inputFilter = $inputFilter->get($fieldsetName);
                }
            }
        }
        
        $elementNameExploded = $this->getFieldPathInArray($element->getName());
        
        $elementName = array_pop($elementNameExploded);
        
        $required = false;
        if ($inputFilter->has($elementName)) {
            $required = $inputFilter->get($elementName)->isRequired();
        }

        return $required;
    }
    
    public function getFieldPathInArray($fieldsetPath)
    {
        $stringPath = $fieldsetPath;
        
        if (substr($stringPath, -1) == ']') {
            $stringPath = substr($stringPath, 0, -1);
        }
        
        $stringPath = str_replace(array('][', '['), "#sep#", $stringPath);
        
        $fieldsetPathArray = explode('#sep#', $stringPath);
        
        return $fieldsetPathArray;
    }

    /**
     * Retourne le tag d'ouverture du fieldset donné
     *
     * @param Fieldset $fieldset
     * @param FormHelper $formHelper
     * @return string
     */
    public function getFieldsetOpenTag(Fieldset $fieldset, FormHelper $formHelper)
    {
        // Attributs du fieldset - Définition de 'id' si nécessaire
        $attributes = $fieldset->getAttributes();
        if (!array_key_exists('id', $attributes) && array_key_exists('name', $attributes)) {
            $attributes['id'] = $attributes['name'];
        }

        return sprintf('<fieldset %s>', $formHelper->createAttributesString($attributes));
    }

    /**
     * Retourne le tag d'ouverture simple d'un fiedlset
     *
     * @return string
     */
    public function getFieldsetSimpleOpenTag()
    {
        return '<fieldset>'. "\n";
    }

    /**
     * Retourne le tag de fermeture du fieldset
     *
     * @return string
     */
    public function getFieldsetCloseTag()
    {
        return '</fieldset>'. "\n";
    }

    /**
     * Rendu des éléments
     *
     * @param Form $form
     * @param array $elementsName
     * @param Fieldset $fieldset
     * @return string
     */
    public function renderElements($elements)
    {
        $html = '';

        // Ouverture du conteneur
        $html .= "<div class=\"container-fluid\">\n";
        
        foreach ($elements as $element) {
            // Traitement des messages d'un champ CSRF
            if ($element instanceof OftCsrf || $element instanceof ZendCsrf) {
                $this->handleCsrf($element);
            }
            
            if ($element instanceof Fieldset) {
                $html .= $this->renderFieldset($element);
                
                continue;
            }

            // Configuration de l'élément
            $required = $this->isElementRequired($element);
            if ($required) {
                $element->setOption('required', true);
            }
            
            
            if ($element->getAttribute('type') === 'hidden') {
                $options = array_merge(
                    $this->options, $element->getOptions()
                );
                
                $html .= $this->view->smartElement($element, $options) . "\n";
            } else {
                $html .= $this->renderElement($element);
            }
        }

        // Fermeture de la dernière ligne
        if ($this->openedRow) {
            $html .= '</div>' . "\n";
        }

        // Fermeture du conteneur
        $html .= '</div>'. "\n";

        return $html;
    }

    public function renderElement(Element $element)
    {
        $html = '';
        
        // Ouverture de la ligne
        if ($this->openedRow === false) {
            $html .= '<div class="form-group">' . "\n";
            $this->openedRow = true;
        }
        
        $options = array_merge(
            $this->options, $element->getOptions()
        );
        
        // Rendu de l'élément
        $html .= $this->view->smartElement($element, $options) . "\n";

        // Fermeture de la ligne
        if ($element->getOption('elm_nl') === false) {
            ;
        } else {
            $html .= '</div>' . "\n";
            $this->openedRow = false;
        }
        
        return $html;
    }

    /**
     * Gère le passage des éventuels messages d'un champ de
     * type CSRF vers l'objet formulaire
     *
     * @param Element $element
     * @param Form $form
     * @return void
     */
    protected function handleCsrf(Element $element)
    {
        $csrfMessages = $element->getMessages();
        
        if (count($csrfMessages) == 0) {
            return;
        }

        // RAZ des messages
        $element->setMessages(array());

        // Remontée des messages au formulaire
        $formMessages = $this->form->getMessages();
        $mergedMessages = array_merge($formMessages, $csrfMessages);
        $this->form->setMessages($mergedMessages);
    }

}
