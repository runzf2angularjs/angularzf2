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

use Zend\Form\Element;
use Zend\I18n\View\Helper\AbstractTranslatorHelper;
use Zend\InputFilter\InputFilterInterface;

class SmartElement extends AbstractTranslatorHelper
{

    /**
     * Valeurs par défaut des attributs de positionnement
     *
     * @var array
     */
    protected $defaultMapAttribs = array(
        'elm_size' => 6,
        'elm_align' => 'left',
        'elm_prefix' => 0,
        'elm_suffix' => 0,
        'label_size' => 6,
        'label_nl' => false,
        'label_align' => 'right',
        'label_prefix' => 0,
        'label_suffix' => 0,
    );

    /**
     * Options par défaut pour le rendu du formulaire
     *
     * @var array
     */
    protected $defaultOptions = array(
        // Séparateur
        'separator' => '&nbsp;:',
        // Afficher symbole champ obligatoire ?
        'show_mandatory_symbol' => true,
        // Symbole champ obligatoire
        'mandatory_symbol' => '*',
        // Messages
        'show_messages' => true,
        // Requis
        'required' => false,
        // Descriptions
        'show_descriptions' => true,
        // Feedback, champs en erreurs colorés
        'show_error_feedback' => true,
        // Classe CSS forcée sur les champs inputs
        'attr_class_input' => 'form-control',
        // Classe CSS forcée sur les champs inputs de type submit
        'attr_class_btn_input' => 'btn btn-default',
        // Classe CSS forcée sur les champs inputs de type button
        'attr_class_btn_submit' => 'btn btn-primary',
        // Classe CSS sur les éléments encadrants les descriptions
        'attr_class_descriptions' => 'help-block',
        // Classe CSS sur les éléments encadrants les messages
        'attr_class_messages' => 'help-block',
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

    /**
     * Flag : ligne ouverte/fermée pour le rendu des éléments
     *
     * @var bool
     */
    protected $openRow = false;

    /**
     * Retourne le code HTML pour l'affichage simplifié d'un formulaire
     *
     * @param Element $form Formulaire
     * @param array $options Options pour le rendu du formulaire
     * @return string
     */
    public function __invoke(Element $element, array $options = array())
    {
        $this->options = $this->defaultOptions;
        
        $this->mergeOptions($options);

        $html = $this->renderElement($element);
 
        return $html;
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
     * Retourne les options fusionnées ou, à défaut, les options par défaut
     *
     * @return array
     */
    public function getOptions()
    {
        if (!$this->options) {
            $this->options = $this->defaultOptions;
        }
        return $this->options;
    }

    /**
     * Retourne vrai si l'option précisée est définie
     *
     * @return bool
     */
    public function hasOption($key)
    {
        return (isset($this->options[$key]) && !empty($this->options[$key]));
    }

    /**
     * Fusionne les attributs de positionnement avec le paramètrage de l'utilisateur
     *
     * @param Element $element
     * @param array $mapAttribs
     * @return array
     */
    public function mergeElementMapAttribs(Element $element, array $mapAttribs = array())
    {
        // Get attribs from element
        $attribs = $element->getOptions();
        foreach ($attribs as $option => $value) {
            if (array_key_exists($option, $mapAttribs)) {
                $mapAttribs[$option] = $value;
            }
        }

        return $mapAttribs;
    }

    /**
     * Retourne TRUE si l'élément est de type Submit, FALSE sinon
     *
     * @param Element $element
     * @return bool
     */
    public function isSubmitElement($element)
    {
        if ($element instanceof Element\Submit) {
            return true;
        }

        $typeAttr = strtolower($element->getAttribute('type'));
        if ($typeAttr == 'submit') {
            return true;
        }

        return false;
    }

    /**
     * Retourne TRUE si l'élément est de type Reset, FALSE sinon
     *
     * @param Element $element
     * @return bool
     */
    public function isResetElement($element)
    {
        $typeAttr = strtolower($element->getAttribute('type'));
        if ($typeAttr == 'reset') {
            return true;
        }

        return false;
    }

    /**
     * Retourne vrai si l'élément a des messages associés, faux sinon
     *
     * @param Element $element
     * @return bool
     */
    public function elementHasMessages(Element $element)
    {
        $messages = $element->getMessages();
        return count($messages) > 0;
    }

    /**
     * Retourne le label échappé, traduit et accompagné de l'étoile (accessible !) si nécessaire
     *
     * @todo Traduction du label et acronyme
     * @param Element $element
     * @param bool $required
     * @return string
     */
    public function getLabelTag(Element $element)
    {
        $html = '';

        $labelContent = $element->getLabel();
        if ($labelContent === null || $labelContent == '') {
            return '';
        }
        
        $content = e($this->getTranslator()->translate($labelContent));
        
        $required = $this->options['required'];
        
        // Tag <abbr>, symbôle "obligatoire"
        if ($required && $this->options['show_mandatory_symbol'] == true) {
            $content .= '&nbsp;<abbr class="required" title="';
            $content .= 'champ obligatoire';
            $content .= '">' . $this->options['mandatory_symbol'] . '</abbr>';
        }

        // Séparateur
        if (is_string($this->options['separator'])) {
            $content .= $this->options['separator'];
        }

        // Attributs du label enregistrés sur l'élément
        $attributes = $element->getLabelAttributes();

        // Attribut 'for'
        $attributes['for'] = $this->view->form()->getId($element);

        // Feedback
        if ($this->options['show_error_feedback'] == true && $this->elementHasMessages($element)) {
            if (!isset($attributes['class'])) {
                $attributes['class'] = '';
            }
            $attributes['class'] .= ' control-label ';
        }

        $html .= $this->view->formLabel()->openTag($attributes);
        $html .= $content;
        $html .= $this->view->formLabel()->closeTag();

        return $html;
    }

    /**
     * Retourne le tag input de l'élément
     *
     * @param Element $element
     * @return string
     */
    public function getInputTag(Element $element)
    {
        $placeholder = $element->getAttribute('placeholder');
        
        if ($placeholder != null) {
            $element->setAttribute('placeholder', $this->getTranslator()->translate($placeholder));
        }
        
        $valueOptions = $element->getOption('value_options');
        if ($valueOptions != null) {
            foreach($valueOptions as $key => $value) {
                $valueOptions[$key] = $this->getTranslator()->translate($value);
            }
            
            $element->setValueOptions($valueOptions);
        }
        
        // Radio, Checkbox, MultiCheckbox
        // Traitement spécifique
        if (    $element instanceof Element\Radio
            ||  $element instanceof Element\MultiCheckbox
            ||  $element instanceof Element\Checkbox
        ) {
            return $this->getRadioAndCheckbox($element);
        }

        // Color, Image, File
        // Pas de personnalisation du tag input
        if (    $element instanceof Element\Color
            ||  $element instanceof Element\Image
            ||  $element instanceof Element\File
        ) {
            return $this->view->formElement($element);
        }

        // Attribut id
        $element->setAttribute('id', $this->view->form()->getId($element));

        if (!$element->hasAttribute('class')) {
            if ($this->isSubmitElement($element)) {
                // Classe CSS des submit
                if ($this->hasOption('attr_class_btn_submit')) {
                    $element->setAttribute('class', $this->options['attr_class_btn_submit']);
                }
            } elseif ($element instanceof Element\Button) {
                // Classe CSS des buttons
                if ($this->hasOption('attr_class_btn_input')) {
                    $element->setAttribute('class', $this->options['attr_class_btn_input']);
                }
            } else {
                // Classe CSS des éléments
                if ($this->hasOption('attr_class_input')) {
                    $element->setAttribute('class', $this->options['attr_class_input']);
                }
            }
        }

        return $this->view->formElement($element);
    }

    /**
     * Retourne la description de l'élément
     *
     * @param Element $element
     * @return string
     */
    public function getDescriptionTag(Element $element)
    {
        $html = '';

        // Description
        $description = $element->getOption('description');

        if ($description) {
            $html .= '<p class="' . $this->options['attr_class_descriptions'] . '">';
            $html .= $description;
            $html .= '</p>';
        }

        return $html;
    }

    /**
     * Retourne les messages associés à l'élément
     *
     * @param Element $element
     * @return string
     */
    public function getMessagesTag(Element $element)
    {
        $html = '';

        $messages = $element->getMessages();
        if (count($messages)) {
            $html .= '<p class="' . $this->options['attr_class_messages'] . '">';
            $html .= implode('<br />', $element->getMessages());
            $html .= '</p>';
        }

        return $html;
    }

    /**
     * Retourne la classe CSS adaptée selon son type et son éventuelle valeur
     *
     * @param string $type
     * @param mixed $value
     * @return string
     */
    public function getCssClass($type, $value = null)
    {
        switch ($type) {
            case 'size' :
                $class = ' col-xs-12 col-sm-' . $value . ' ';
                break;
            case 'prefix' :
                $class = ' col-sm-offset-' . $value . ' ';
                break;
            case 'push' :
                $class = ' col-sm-push-' . $value . ' ';
                break;
            case 'align' :
                $class = ' text-' . $value . ' ';
                break;
            case 'error-feedback' :
                $class = ' has-error ';
                break;
            default :
                $class = '';
        }

        return $class;
    }

    /**
     * Retourne le rendu du bloc "label" + "element"
     * Le bloc "element" contient l'élément, la description et les messages d'erreurs
     *
     * @param Element $element
     * @param bool $required
     * @param bool $isLastElement
     * @return string
     */
    public function renderElement(Element $element)
    {
        $html = '';

        if ($element->getAttribute('type') === 'hidden') {
            return $this->view->formElement($element) . "\n";
        }

        $mapAttribs = $this->defaultMapAttribs;
        
        // Positionnement des boutons
        if ($this->isSubmitElement($element) || $this->isResetElement($element)) {
            // Alignement du bouton sous les champs
            $mapAttribs['elm_prefix'] = $mapAttribs['label_size'];
            $mapAttribs['label_size'] = 0;
        }

        $mapAttribs = $this->mergeElementMapAttribs($element, $mapAttribs);
        
        // Si le label est souhaité
        if ($mapAttribs['label_size']) {
            // Classe de base
            $labelClass = '';
            // Feedback
            if ($this->options['show_error_feedback'] == true && $this->elementHasMessages($element)) {
                $labelClass .= $this->getCssClass('error-feedback');
            }
            // Largeur
            if ($mapAttribs['label_size']) {
                $labelClass .= $this->getCssClass('size', $mapAttribs['label_size']);
            }
            // Préfix
            if ($mapAttribs['label_prefix']) {
                $labelClass .= $this->getCssClass('prefix', $mapAttribs['label_prefix']);
            }
            // Suffix
            if ($mapAttribs['label_suffix']) {
                $labelClass .= $this->getCssClass('push', $mapAttribs['label_suffix']);
            }
            // Alignement
            if ($mapAttribs['label_align']) {
                $labelClass .= $this->getCssClass('align', $mapAttribs['label_align']);
            }

            $labelTag = $this->getLabelTag($element);
            if (!empty($labelTag)) {
                $html .= '<div class="' . $labelClass . ' text-align-responsive">' . "\n";
                $html .= $labelTag . "\n";
                $html .= '</div>' . "\n";
            }
        }
        
        if ($mapAttribs['label_nl']) {
            $html .= '<div style="clear:both">' . "\n";
        }

        // Si l'élément est souhaité
        if ($mapAttribs['elm_size']) {
            // Classe de base
            $elmClass = '';
            // Feedback
            if ($this->options['show_error_feedback'] == true && $this->elementHasMessages($element)) {
                $elmClass .= $this->getCssClass('error-feedback');
            }
            // Largeur
            if ($mapAttribs['elm_size']) {
                $elmClass .= $this->getCssClass('size', $mapAttribs['elm_size']);
            }
            // Préfix
            if ($mapAttribs['elm_prefix']) {
                $elmClass .= $this->getCssClass('prefix', $mapAttribs['elm_prefix']);
            }
            // Suffix
            if ($mapAttribs['elm_suffix']) {
                $elmClass .= $this->getCssClass('push', $mapAttribs['elm_suffix']);
            }
            // Alignement
            if ($mapAttribs['elm_align']) {
                $elmClass .= $this->getCssClass('align', $mapAttribs['elm_align']);
            }

            $html .= '<div class="' . $elmClass . ' text-align-responsive">'."\n";

            // Tag input
            $html .= $this->getInputTag($element) . "\n";

            // Description
            if ($this->options['show_descriptions'] == true && $this->getDescriptionTag($element) != '') {
                $html .= $this->getDescriptionTag($element) . "\n";
            }

            // Messages
            if ($this->options['show_messages'] == true && $this->getMessagesTag($element) != '') {
                $html .= $this->getMessagesTag($element) . "\n";
            }

            $html .= '</div>';
        }
        
        
        if ($mapAttribs['label_nl']) {
            $html .= '</div>';
        }
        
       
        return $html;
    }

    /**
     * Traitements spécifiques pour les éléments Radio et MultiCheckbox pour conformité markup TB3
     *
     * Un tag label supplémentaire est ajouté autour de chaque option (ZF)
     * Ce tag label doit porter une classe spécifique et sera plus tard remplacé par une div
     * "str_replace" = solution cheap
     * Piste d'amélioration : surcharges des helpers Radio, et MultiCheckbox
     *
     * @param Element $element
     * @return string
     */
    public function getRadioAndCheckbox(Element $element)
    {
        // Option d'affichage en ligne
        if ($element instanceof Element\Radio || $element instanceof Element\MultiCheckbox) {
            $suffixInline = '';
            $inline = $element->getOption('inline');
            if ($inline == true) {
                $suffixInline = '-inline';
            }
        }

        // Classe spécifique markup TB3
        if ($element instanceof Element\Radio) {
            $this->view
                ->plugin('form_radio')
                ->setLabelAttributes(array(
                    'class' => 'radio' . $suffixInline,
            ));
        }

        // Classe spécifique markup TB3
        if ($element instanceof Element\MultiCheckbox) {
            $this->view
                ->plugin('form_multi_checkbox')
                ->setLabelAttributes(array(
                    'class' => 'checkbox' . $suffixInline,
            ));
        }

        return str_replace(
            array('<label', '</label>'),
            array('<div ', '</div>'),
            $this->view->formElement($element)
        );
    }

}
