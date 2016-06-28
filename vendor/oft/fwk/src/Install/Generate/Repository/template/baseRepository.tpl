<?php

/* @var $this Oft\View\View */

echo "<?php\n";
?>

/**
 * ATTENTION : CETTE CLASSE NE DOIT PAS ÊTRE MODIFIÉE
 *
 * Cette classe a été générée automatiquement et est susceptible d'être écrasée
 */

namespace <?=$namespace?>;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder as DoctrineQueryBuilder;
use DomainException;
use Oft\Db\EntityQueryBuilder;
use Oft\Entity\BaseEntity;
use Oft\Mvc\Application;
use Oft\Paginator\Adapter\QueryBuilder;
use Zend\Paginator\Paginator;

abstract class <?=$className?>
{

    /**
     * Nom de la table
     *
     * @var string
     */
    public static $table = '<?=$tableName?>';

    /**
     * Clef(s) primaire(s) de la table
     *
     * @var array
     */
    public static $primary = array(<?php
        foreach ($description->primary as $field) :
            echo
                "\n" .
                '        \'' . $field . '\',';
        endforeach;
        echo "\n";
    ?>
    );

    /**
     * Description des colonnes de la table
     *
     * @var array
     */
    public static $metadata = array(<?php
        foreach ($description->columns as $field => $desc) :
            echo
                "\n" .
                '        \'' . $field . '\' => array(';
            foreach ($desc as $key => $value) :
                echo
                    "\n" .
                    '            \''. $key . '\'' .
                    ' => ' . var_export($value, true) . ',';
            endforeach;
            echo
                "\n" .
                '        ),';
        endforeach;
        echo "\n";
    ?>
    );

    /**
     * Connexion à la base de données
     *
     * @var Connection
     */
    protected $db;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->db = $app->db;
    }

    /**
     * Retourne une entité initialisée à partir des valeurs par défaut du SGBD
     *
     * @param array $data = null
     * @return BaseEntity
     */
    public function getNew(array $data = null)
    {
        $defaults = self::getDefaultValues();

        if ($data !== null) {
            foreach ($data as $key => $value) {
                if (!array_key_exists($key, $defaults)) {
                    continue;
                }

                $defaults[$key] = $value;
            }
        }

        return new BaseEntity($defaults, true);
    }

    /**
     * Retourne une entité initialisée avec des valeurs par défaut vides.
     *
     * @return BaseEntity
     */
    public function getNewEmpty()
    {
        $defaults = array_combine(
            array_keys(self::$metadata),
            array_fill(0, count(self::$metadata), null)
        );

        return new BaseEntity($defaults, true);
    }

    /**
     * Construction de la requête de sélection d'éléments.
     *
     * Mode non strict :
     *   - les éléments sont retournés tels que renvoyés par le SGBD
     * Mode strict :
     *   - les éléments récupérés disposent des valeurs par défaut définies dans les métadonnées,
     *   - il est impossible de retourner des valeurs qui ne font pas partie des clefs par défaut fournies
     *
     * @param array $options
     * @param bool $strict = false
     * @return EntityQueryBuilder
     */
    public function getQueryBuilder(array $options = array(), $strict = false)
    {
        if (!isset($options['columns'])) {
            $options['columns'] = array_keys(self::$metadata);
        }

        if (!isset($options['sort'])) {
            $options['sort'] = array('<?=$primary[0]?>' => 'asc');
        }

        $options['table'] = self::$table;

        $queryBuilder = new EntityQueryBuilder($this->db);
        $queryBuilder->applyOptions($options);

        if ($strict) {
            $queryBuilder->setFetchArgs(array(
                self::getDefaultValues(), // valeurs par défaut issues des métadonnées
                true // strict
            ));
        } else {
            $queryBuilder->setFetchArgs(array(
                array(), // pas de valeurs par défaut
                false // non strict
            ));
        }

        return $queryBuilder;
    }

    /**
     * Retourne un élément en fonction de sa/ses clef(s) primaire(s)
     *
<?php
    $args = '';
    foreach ($primary as $column) :
        $varType = $description->columns[$column]['type'];
        echo
            '     * @param ' . $varType .
            ' $' . $column . "\n";
    endforeach;
?>
     * @return BaseEntity
     * @throws DomainException
     */
    public function getById(<?php
        $args = '';
        foreach ($primary as $column) :
            $args .= '$' . $column . ', ';
        endforeach;
        echo substr($args, 0, -2);
    ?>)
    {
        $options = array(
            'filters' => array(<?php
            foreach ($primary as $column) :
                echo
                    "\n" .
                    '                array(' .
                    "\n" .
                    '                    \'field\'' .
                    ' => ' .
                    '\'' . $column . '\',' .
                    "\n" .
                    '                    \'value\'' .
                    ' => ' .
                    '$' . $column . ',' .
                    "\n" .
                    '                    \'operator\'' .
                    ' => ' .
                    '\'=\',' .
                    "\n" .
                    '                ),';
            endforeach;
            echo "\n";
        ?>
            ),
        );

        $queryBuilder = $this->getQueryBuilder($options, true);
        $statement = $queryBuilder->execute();
        $data = $statement->fetch();

        if (!$data instanceof BaseEntity) {
            throw new DomainException("L'élément n'a pas été trouvé");
        }

        $data->setUpdatedFields();

        return $data;
    }

    /**
     * Retourne un objet paginé des éléments en fonction des filtres donnés
     *
     * @param array $options
     * @return Paginator
     */
    public function getPaginator(array $options = array())
    {
        $queryBuilder = $this->getQueryBuilder($options, false);

        $countQuery = clone $queryBuilder;
        $countQuery->resetQueryPart('select')
            ->resetQueryPart('groupBy')
            ->select('COUNT(*)');

        $adapter = new QueryBuilder($queryBuilder);
        $adapter->setCountQuery($countQuery);

        return new Paginator($adapter);
    }

    /**
     * Mise à jour d'un élément
     *
     * @param BaseEntity $entity
     * @return int
     */
    public function update(BaseEntity $entity)
    {
        $updateConstraint = array(<?php
                foreach ($primary as $column) :
                    echo
                        "\n" .
                        '            \'' . $column . '\'' .
                        ' => ' .
                        '$entity[\'' . $column . '\'],';
                endforeach;
                echo "\n";
            ?>
        );

        $updateData = $entity->getUpdatedFields();

        // si les données n'ont pas étés modifiées on retourne
        if (!count($updateData)) {
            return true;
        }
        <?php
            foreach ($primary as $column) :
                echo "\n" . '        unset($updateData[\'' . $column . '\']);';
            endforeach;
            echo "\n";
        ?>

        return $this->db->update(self::$table, $updateData, $updateConstraint);
    }

    /**
     * Création d'un élément
     *
     * @param BaseEntity $entity
     * @return int
     */
    public function insert(BaseEntity $entity)
    {
        $insertData = $entity->getArrayCopy();<?php
            foreach ($primary as $column) :
                if ($description->columns[$column]['identity']) {
                    echo
                        "\n" .
                        '        unset($insertData[\'' . $column . '\']);';
                }
            endforeach;
            echo "\n";
        ?>

        return $this->db->insert(self::$table, $insertData);
    }

    /**
     * Suppression d'un élément
     *
     * @param BaseEntity $entity
     * @return int
     */
    public function delete(BaseEntity $entity)
    {
        $deleteConstraint = array(<?php
                foreach ($primary as $column) :
                    echo
                        "\n" .
                        '            \'' . $column . '\'' .
                        ' => ' .
                        '$entity[\'' . $column . '\'],';
                endforeach;
                echo "\n";
            ?>
        );

        return $this->db->delete(self::$table, $deleteConstraint);
    }

    protected static function getDefaultValues()
    {
        $defaults = array();

        foreach (self::$metadata as $column => $metadatum) {
            $defaults[$column] = isset($metadatum['default']) ? $metadatum['default'] : null;
        }

        return $defaults;
    }
}
