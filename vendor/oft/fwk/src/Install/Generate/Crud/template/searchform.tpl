<?php

/* @var $this Oft\View\View */

echo "<?php\n";
?>

namespace <?=$namespace?>;

class <?=$className?> extends <?=$formClassName?> 
{

    /**
     * @param BaseEntity $entity
     * @param array $options
     */
    public function __construct($entity, array $options = array())
    {
        parent::__construct($entity, $options, '<?=$formName?>');
        
        // Retrait du/des champ(s) de la clef primaire <?php
            foreach ($primary as $column) {
                echo "\n" . '        $this->remove(\'' . $column . '\');' . "\n";
            }
        ?>

        // Ajout du bouton de réinitialisation
        $this->addReset();

        // Les champs ne sont pas obligatoires dans la recherche
        $this->setAllowEmpty();
    }

    /**
     * Ajoute l'élément de formulaire "reset_search"
     */
    protected function addReset()
    {
        $this->add(array(
            'name' => 'reset_search',
            'type' => 'Button',
            'value' => 1,
            'attributes' => array(
                'type' => 'submit',
            ),
            'options' => array(
                'label' => 'Réinitialiser',
                'elm_prefix' => 0,
                'elm_size' => 2,
            ),
        ));
    }

    /**
     * Retourne les données préalablement validées du formulaire
     * au format adapté pour la construction d'une requête
     *
     * @return array
     */
    public function getSearchData()
    {
        $searchData = array();
        foreach ($this->getObject()->getArrayCopy() as $field => $value) {
            if ($value === '' || $value === null) { // '0' et 0 OK
                continue;
            }

            $searchData[] = array(
                'field' => $field,
                'operator' => 'LIKE',
                'value' => $value,
            );
        }

        return $searchData;
    }

    /**
     * Autorise une saisie vide dans les champs du formulaire de recherche
     */
    protected function setAllowEmpty()
    {
        foreach ($this->getElements() as $element) {
            $name = $element->getName();
            if (!$this->filter->has($name)) {
                continue;
            }

            $this->filter->get($element->getName())->setRequired(false);
        }
    }

}
