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

namespace Oft\Install\Tools\MySql;

use Doctrine\DBAL\Connection;
use DomainException;
use InvalidArgumentException;

class TableDescription
{

    /**
     * Connexion
     *
     * @var Connection
     */
    protected $db;

    /**
     * Nom de la base de données
     *
     * @var string
     */
    protected $dbName;

    /**
     * Nom de la table ciblée
     *
     * @var string
     */
    protected $tableName;

    /**
     * Colonnes de la table
     *
     * @var array
     */
    public $columns = array();

    /**
     * Clef(s) primaire(s) de la table
     *
     * @var array
     */
    public $primary = array();

    /**
     * Description des éléments de formulaires
     *
     * @var array
     */
    public $formElements = array();

    /**
     * Type d'élément par défaut
     *
     * @var string
     */
    protected $defaultType = 'Zend\Form\Element\Text';

    /**
     * Validateurs par défaut
     *
     * @var array
     */
    protected $defaultValidators = array();

    /**
     * Filtres par défaut
     *
     * @var array
     */
    protected $defaultFilters = array(
        array('class' => 'Zend\Filter\StripTags'),
        array('class' => 'Zend\Filter\StringTrim'),
    );

    /**
     * Requête : existence d'une table
     *
     * @var string
     */
    const QUERY_EXISTS = <<<EOT
SELECT
  COUNT(*) as count
FROM
  information_schema.tables
WHERE
  table_schema = :schema
  AND table_name = :table
EOT;

    /**
     * Requête : informations sur les colonnes d'une table
     *
     * @var string
     */
    const QUERY_INFOS = <<<EOT
SELECT
  `COLUMNS`.`COLUMN_NAME`, -- Nom
  `COLUMNS`.`COLUMN_DEFAULT`, -- Valeur par défaut
  `COLUMNS`.`IS_NULLABLE`, -- Null or not null
  `COLUMNS`.`COLUMN_TYPE`, -- Type, longueur, *signed
  `COLUMNS`.`COLUMN_KEY`, -- Clef : PRI, et autres
  `COLUMNS`.`EXTRA`, -- Auto-incrément

  `KEY_COLUMN_USAGE`.`REFERENCED_TABLE_NAME`, -- Si FK : table de référence, sinon NULL
  `KEY_COLUMN_USAGE`.`REFERENCED_COLUMN_NAME` -- Si FK : colonne de référence, sinon NULL

FROM
  `information_schema`.`COLUMNS`
  LEFT JOIN `information_schema`.`KEY_COLUMN_USAGE`
    ON `KEY_COLUMN_USAGE`.`COLUMN_NAME` = `COLUMNS`.`COLUMN_NAME`
    AND `KEY_COLUMN_USAGE`.`TABLE_SCHEMA` = `COLUMNS`.`TABLE_SCHEMA`
    AND `KEY_COLUMN_USAGE`.`TABLE_NAME` = `COLUMNS`.`TABLE_NAME`
    AND `KEY_COLUMN_USAGE`.`REFERENCED_TABLE_NAME` IS NOT NULL -- Uniquement les FK

WHERE
  `COLUMNS`.`TABLE_SCHEMA` = :schema -- Schéma
  AND `COLUMNS`.`TABLE_NAME` = :table -- Table

ORDER BY `COLUMNS`.`ORDINAL_POSITION` -- Ordre d'origine des champs de la table
EOT;

    /**
     * @param Connection $db
     * @param string $tableName
     * @throws InvalidArgumentException
     */
    public function __construct(Connection $db, $tableName)
    {        
        $this->db = $db;

        $this->dbName = $db->getDatabase();
        $this->tableName = $tableName;

        if (!$this->checkTableExists()) {
            throw new InvalidArgumentException('La table ' . $tableName . ' n\'a pas été trouvée');
        }

        $this->init();
    }

    /**
     * Vérifie l'existence d'une table
     *
     * @return bool
     */
    protected function checkTableExists()
    {
        $stmt = $this->db->prepare(self::QUERY_EXISTS);

        $stmt->execute(array(
            'schema' => $this->dbName,
            'table' => $this->tableName,
        ));

        $result = $stmt->fetch();

        return (bool)$result['count'];
    }

    /**
     * Collecte les informations
     *
     * @throws DomainException
     */
    protected function init()
    {
        $stmt = $this->db->prepare(self::QUERY_INFOS);

        $stmt->execute(array(
            'schema' => $this->dbName,
            'table' => $this->tableName,
        ));

        foreach ($stmt as $row) {
            $columnName = $row['COLUMN_NAME'];            
            $metadata = $this->getMetadata($row);
            
            $this->columns[$columnName] = $metadata;
            $this->formElements[$columnName] = array(
                'type' => $this->getFormElementType($metadata['type'], $metadata['identity']),
                'input_filter' => $this->getFormElementInputFilter($metadata),
                'required' => $this->getFormElementRequired($metadata),
            );
        }

        if (count($this->primary) === 0) {
            throw new DomainException('La table ' . $this->tableName . ' ne comporte pas de clef primaire');
        }
    }

    /**
     * Retourne les méta-données de la table
     *
     * @param array $row
     * @return array
     */
    protected function getMetadata(array $row)
    {
        $options = array();

        // Attribut unsigned
        if (strpos($row['COLUMN_TYPE'], 'unsigned') !== false) {
            $options['unsigned'] = true;
        }

        // Types
        if (preg_match('/^((?:var)?char)\((\d+)\)/', $row['COLUMN_TYPE'], $matches)) {
            $row['COLUMN_TYPE'] = $matches[1];
            $options['length'] = (int)$matches[2];
        } else if (preg_match('/^decimal\((\d+),(\d+)\)/', $row['COLUMN_TYPE'], $matches)) {
            $row['COLUMN_TYPE'] = 'decimal';
            $options['precision'] = (int)$matches[1];
            $options['scale'] = (int)$matches[2];
        } else if (preg_match('/^float\((\d+),(\d+)\)/', $row['COLUMN_TYPE'], $matches)) {
            $row['COLUMN_TYPE'] = 'float';
            $options['precision'] = (int)$matches[1];
            $options['scale'] = (int)$matches[2];
        } else if (preg_match('/^((?:big|medium|small|tiny)?int)\((\d+)\)/', $row['COLUMN_TYPE'], $matches)) {
            $row['COLUMN_TYPE'] = $matches[1]; // On ne tient pas compte de la longueur d'un *int
        }

        // Clef primaire
        $identity = false;
        if (strtoupper($row['COLUMN_KEY']) === 'PRI') {
            if ($row['EXTRA'] === 'auto_increment') {
                $identity = true;
            }
            $this->primary[] = $row['COLUMN_NAME'];
        }

        $infos = array(
            'type' => $row['COLUMN_TYPE'],
            'default' => $row['COLUMN_DEFAULT'],
            'nullable' => (bool)($row['IS_NULLABLE'] == 'YES'),
            'identity' => $identity,
            'foreign_key_table' => $row['REFERENCED_TABLE_NAME'],
            'foreign_key_column' => $row['REFERENCED_COLUMN_NAME'],
        );

        return array_merge($infos, $options);
    }

    /**
     * Retourne le caractère obligatoire du champ ou non
     *
     * @param array Metadonnées de la colonne
     * @return bool
     */
    protected function getFormElementRequired($metadata)
    {
        // "not nullable" = non vide
        if (isset($metadata['nullable']) && $metadata['nullable'] == false) {
            return true;
        }

        return false;
    }

    /**
     * Retourne les règles filtres et validateurs pour un élément donné
     *
     * @param array Metadonnées de la colonne
     * @return array
     */
    protected function getFormElementInputFilter($metadata)
    {
        $inputFilter = array(
            'validators' => array(),
            'filters' => array(),
        );
        
        // Cas particuliers : date, datetime, time & timestamp : aucune règle
        if (in_array($metadata['type'], array('date', 'datetime', 'time', 'timestamp'))) {
            return $inputFilter;
        }

        // Règles par défaut
        $inputFilter['validators'] = $this->defaultValidators;
        $inputFilter['filters'] = $this->defaultFilters;

        // "unsigned" = valeur supérieure à 0
        if (isset($metadata['unsigned']) && $metadata['unsigned'] == true) {
            $inputFilter['validators'][] = array(
                'class' => 'Zend\Validator\GreaterThan',
                'params' => array(
                    'min' => 0
                ),
            );
        }

        // Selon le type
        if (strpos($metadata['type'], 'char') !== false) {
            $inputFilter['validators'][] = array(
                'class' => 'Zend\Validator\StringLength',
                'params' => array(
                    'max' => $metadata['length'],
                ),
            );
        } else if ($metadata['type'] == 'decimal' || $metadata['type'] == 'float') {
            // Les validateurs et filtres sont gérés par l'élément de formulaire
            $inputFilter['validators'] = array();
            $inputFilter['filters'] = array();
        } else if (strpos($metadata['type'], 'int') !== false) {
            $inputFilter['validators'][] = array(
                'class' => ($metadata['identity']) ? 'Zend\Validator\Digits' : 'Zend\I18n\Validator\IsInt',
            );
        }
        
        return $inputFilter;
    }

    /**
     * Retourne le type d'élément
     *
     * @param string $type Type de champs en base de données
     * @param bool $identity Flag : champ identifiant
     * @return string
     */
    protected function getFormElementType($type, $identity)
    {
        if ($identity === true) {
            return 'Zend\Form\Element\Hidden';
        }

        switch ($type) {
            case 'decimal' :
            case 'float' :
                $type = 'Oft\Form\Element\Float';
                break;
            case 'datetime' :
            case 'timestamp' :
                $type = 'Oft\Form\Element\DateTime';
                break;
            case 'date' :
                $type = 'Oft\Form\Element\Date';
                break;
            case 'time' :
                $type = 'Oft\Form\Element\Time';
                break;
            default :
                $type = $this->defaultType;
        }

        return $type;
    }

}
