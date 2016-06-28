<?php

/* @var $this Oft\View\View */

echo "<?php\n";
?>

namespace <?=$namespace?>;

use Oft\Entity\BaseEntity;
use Oft\Form\Form;

class <?=$className?> extends Form
{

    /**
     * @param BaseEntity $entity
     * @param array $options
     * @param string $name
     */
    public function __construct($entity, array $options = array(), $name = '<?=$formName?>')
    {
        parent::__construct($name, $options);

        // Ajout des champs au formulaire <?php
            echo "\n";
            foreach ($fields as $field => $options) {
                $methodName = 'add' . ucfirst(str_replace('_', '', \Oft\Util\String::dashToCamelCase($field)));
                echo '        $this->' . $methodName . '();' . "\n";
            }
        ?>
        $this->addSubmit();

        $this->bind($entity);
    }

    /**
     * Passe le formulaire en mode "création"
     */
    public function setCreateForm()
    {<?php
            echo "\n";
            foreach ($primary as $column) {
                if ($metadata[$column]['identity']) {
                    echo '        $this->remove(\'' . $column . '\');' . "\n";
                }
            }
        ?>
    }

    /**
     * Passe le formulaire en mode "mise à jour"
     */
    public function setUpdateForm()
    {<?php
            echo "\n";
            foreach ($primary as $column) {
                if (!$metadata[$column]['identity']) {
                    echo '        $this->remove(\'' . $column . '\');' . "\n";
                }
            }
        ?>
    }
<?php

    function displayElementRules($rules)
    {
        $sRules = '';
        if (count($rules)) {
            foreach ($rules as $rule) {
                $sRules .= "\n";
                $sRules .= '                    new \\' . $rule['class'] . '(';
                if (isset($rule['params'])) {
                    $sRules .= 'array(';
                    foreach ($rule['params'] as $param => $value) {
                        $sRules .=
                            "\n" . '                        ' .
                            '\'' . $param . '\' => ' . $value . ',';
                    }
                    $sRules .= "\n" . '                    )),';
                } else {
                    $sRules .= '),';
                }
            }
            $sRules .= "\n" . '                ';
        }

        return $sRules;
    }

    foreach ($fields as $field => $options) {

        $required = ($options['required']) ? 'true' : 'false';

        echo '
    /**
     * Ajoute l\'élément de formulaire "' . $field . '"
     */
    protected function add' . ucfirst(str_replace('_', '', \Oft\Util\String::dashToCamelCase($field))) . '()
    {
        $this->add(array(
            \'name\' => \'' . $field . '\',
            \'type\' => \'' . $options['type'] . '\',
            \'options\' => array(
                \'label\' => \'' . $field . '\',
                \'label_size\' => 4,
                \'elm_size\' => 8,
            ),';


        echo '
            \'input_filter\' => array(
                \'required\' => ' . $required . ',';
if (count($options['input_filter']['filters']) > 0) {
            echo '
                \'filters\' => array(' . displayElementRules($options['input_filter']['filters']) . '),';
}
if (count($options['input_filter']['validators']) > 0) {
            echo '
                \'validators\' => array(' . displayElementRules($options['input_filter']['validators']) . '),';
}
        echo '
            ),';


        echo '
        ));
    }
        ';
    }
?>

    /**
     * Ajoute l'élément de formulaire "submit"
     */
    protected function addSubmit()
    {
        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Soumettre',
            ),
            'options' => array(
                'elm_prefix' => 4,
                'elm_size' => 2,
                'elm_nl' => false,
            ),
        ));
    }

    /**
     * Définit les attributs nécessaires pour l'auto-complétion
     * sur les champs spécifiés dans l'attribut $fieldsAutocomplete
     *
     * @param string $url
     */
    public function setAutocompleteField($formField, $url)
    {
        $this->get($formField)
            ->setAttribute('data-ac-url', $url)
            ->setAttribute('data-ac-field', $formField);
    }
}
