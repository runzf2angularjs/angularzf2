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

namespace Oft\Test\Install\Tools\MySql;

use ArrayObject;
use Mockery;
use Oft\Install\Tools\MySql\TableDescription;
use PHPUnit_Framework_TestCase;

class TableDescriptionTest extends PHPUnit_Framework_TestCase
{

    /**
     * Définitions de colonnes
     * 
     * @var array
     */
    protected $columnsDefinition = array(
        array(
            'COLUMN_NAME' => 'primary',
            'COLUMN_DEFAULT' => null,
            'IS_NULLABLE' => 'NO',
            'COLUMN_TYPE' => 'int(10) unsigned',
            'COLUMN_KEY' => 'PRI',
            'EXTRA' => 'auto_increment',
            'REFERENCED_TABLE_NAME' => null,
            'REFERENCED_COLUMN_NAME' => null,
        ),
        array(
            'COLUMN_NAME' => 'two',
            'COLUMN_DEFAULT' => 'default',
            'IS_NULLABLE' => 'YES',
            'COLUMN_TYPE' => 'varchar(150)',
            'COLUMN_KEY' => 'UNI',
            'EXTRA' => '',
            'REFERENCED_TABLE_NAME' => 'ref_table_test',
            'REFERENCED_COLUMN_NAME' => 'ref_column_test',
        ),
        array(
            'COLUMN_NAME' => 'three',
            'COLUMN_DEFAULT' => '0.00',
            'IS_NULLABLE' => 'NO',
            'COLUMN_TYPE' => 'decimal(1,2)',
            'COLUMN_KEY' => '',
            'EXTRA' => '',
            'REFERENCED_TABLE_NAME' => null,
            'REFERENCED_COLUMN_NAME' => null,
        ),
        array(
            'COLUMN_NAME' => 'four',
            'COLUMN_DEFAULT' => '0.00',
            'IS_NULLABLE' => 'NO',
            'COLUMN_TYPE' => 'decimal(4,2)',
            'COLUMN_KEY' => '',
            'EXTRA' => '',
            'REFERENCED_TABLE_NAME' => null,
            'REFERENCED_COLUMN_NAME' => null,
        ),
        array(
            'COLUMN_NAME' => 'five',
            'COLUMN_DEFAULT' => '0.00',
            'IS_NULLABLE' => 'NO',
            'COLUMN_TYPE' => 'float(4,2)',
            'COLUMN_KEY' => '',
            'EXTRA' => '',
            'REFERENCED_TABLE_NAME' => null,
            'REFERENCED_COLUMN_NAME' => null,
        ),
        array(
            'COLUMN_NAME' => 'six',
            'COLUMN_DEFAULT' => NULL,
            'IS_NULLABLE' => 'NO',
            'COLUMN_TYPE' => 'datetime',
            'COLUMN_KEY' => '',
            'EXTRA' => '',
            'REFERENCED_TABLE_NAME' => null,
            'REFERENCED_COLUMN_NAME' => null,
        ),
        array(
            'COLUMN_NAME' => 'seven',
            'COLUMN_DEFAULT' => NULL,
            'IS_NULLABLE' => 'NO',
            'COLUMN_TYPE' => 'date',
            'COLUMN_KEY' => '',
            'EXTRA' => '',
            'REFERENCED_TABLE_NAME' => null,
            'REFERENCED_COLUMN_NAME' => null,
        ),
        array(
            'COLUMN_NAME' => 'eight',
            'COLUMN_DEFAULT' => NULL,
            'IS_NULLABLE' => 'YES',
            'COLUMN_TYPE' => 'time',
            'COLUMN_KEY' => '',
            'EXTRA' => '',
            'REFERENCED_TABLE_NAME' => null,
            'REFERENCED_COLUMN_NAME' => null,
        ),
        array(
            'COLUMN_NAME' => 'nine',
            'COLUMN_DEFAULT' => NULL,
            'IS_NULLABLE' => 'YES',
            'COLUMN_TYPE' => 'timestamp',
            'COLUMN_KEY' => '',
            'EXTRA' => '',
            'REFERENCED_TABLE_NAME' => null,
            'REFERENCED_COLUMN_NAME' => null,
        ),
    );

    /**
     * Résultats pour les définitions de colonnes
     *
     * @var array
     */
    protected $descriptionResults = array(
        'columns' => array(
            'primary' => array(
                'type' => 'int',
                'default' => NULL,
                'nullable' => false,
                'identity' => true,
                'foreign_key_table' => NULL,
                'foreign_key_column' => NULL,
                'unsigned' => true,
            ),
            'two' => array(
                'type' => 'varchar',
                'default' => 'default',
                'nullable' => true,
                'identity' => false,
                'foreign_key_table' => 'ref_table_test',
                'foreign_key_column' => 'ref_column_test',
                'length' => 150,
            ),
            'three' => array(
                'type' => 'decimal',
                'default' => '0.00',
                'nullable' => false,
                'identity' => false,
                'foreign_key_table' => NULL,
                'foreign_key_column' => NULL,
                'precision' => 1,
                'scale' => 2,
            ),
            'four' => array(
                'type' => 'decimal',
                'default' => '0.00',
                'nullable' => false,
                'identity' => false,
                'foreign_key_table' => NULL,
                'foreign_key_column' => NULL,
                'precision' => 4,
                'scale' => 2,
            ),
            'five' => array(
                'type' => 'float',
                'default' => '0.00',
                'nullable' => false,
                'identity' => false,
                'foreign_key_table' => NULL,
                'foreign_key_column' => NULL,
                'precision' => 4,
                'scale' => 2,
            ),
            'six' => array(
                'type' => 'datetime',
                'default' => null,
                'nullable' => false,
                'identity' => false,
                'foreign_key_table' => NULL,
                'foreign_key_column' => NULL,
            ),
            'seven' => array(
                'type' => 'date',
                'default' => null,
                'nullable' => false,
                'identity' => false,
                'foreign_key_table' => NULL,
                'foreign_key_column' => NULL,
            ),
            'eight' => array(
                'type' => 'time',
                'default' => null,
                'nullable' => true,
                'identity' => false,
                'foreign_key_table' => NULL,
                'foreign_key_column' => NULL,
            ),
            'nine' => array(
                'type' => 'timestamp',
                'default' => null,
                'nullable' => true,
                'identity' => false,
                'foreign_key_table' => NULL,
                'foreign_key_column' => NULL,
            ),
        ),
        'primary' => array(
            0 => 'primary',
        ),
        'formElements' => array(
            'primary' => array(
                'type' => 'Zend\Form\Element\Hidden',
                'required' => true,
                'input_filter' => array(
                    'validators' => array(
                        array(
                            'class' => 'Zend\Validator\GreaterThan',
                            'params' => array(
                                'min' => 0,
                            ),
                        ),
                        array(
                            'class' => 'Zend\Validator\Digits',
                        ),
                    ),
                    'filters' => array(
                        array(
                            'class' => 'Zend\Filter\StripTags',
                        ),
                        array(
                            'class' => 'Zend\Filter\StringTrim',
                        ),
                    ),
                ),
            ),
            'two' => array(
                'type' => 'Zend\Form\Element\Text',
                'required' => false,
                'input_filter' => array(
                    'validators' => array(
                        array(
                            'class' => 'Zend\Validator\StringLength',
                            'params' => array(
                                'max' => 150,
                            ),
                        ),
                    ),
                    'filters' => array(
                        array(
                            'class' => 'Zend\Filter\StripTags',
                        ),
                        array(
                            'class' => 'Zend\Filter\StringTrim',
                        ),
                    ),
                ),
            ),
            'three' => array(
                'type' => 'Oft\Form\Element\Float',
                'required' => true,
                'input_filter' => array(
                    // Les validateurs et filtres sont gérés par l'élément de formulaire
                    'validators' => array(),
                    'filters' => array(),
                ),
            ),
            'four' => array(
                'type' => 'Oft\Form\Element\Float',
                'required' => true,
                'input_filter' => array(
                    // Les validateurs et filtres sont gérés par l'élément de formulaire
                    'validators' => array(),
                    'filters' => array(),
                ),
            ),
            'five' => array(
                'type' => 'Oft\Form\Element\Float',
                'required' => true,
                'input_filter' => array(
                    // Les validateurs et filtres sont gérés par l'élément de formulaire
                    'validators' => array(),
                    'filters' => array(),
                ),
            ),
            'six' => array(
                'type' => 'Oft\Form\Element\DateTime',
                'required' => true,
                'input_filter' => array(
                    'validators' => array(),
                    'filters' => array(),
                ), // No filters & validators for date* elements
            ),
            'seven' => array(
                'type' => 'Oft\Form\Element\Date',
                'required' => true,
                'input_filter' => array(
                    'validators' => array(),
                    'filters' => array(),
                ), // No filters & validators for date* elements
            ),
            'eight' => array(
                'type' => 'Oft\Form\Element\Time',
                'required' => false,
                'input_filter' => array(
                    'validators' => array(),
                    'filters' => array(),
                ), // No filters & validators for date* elements
            ),
            'nine' => array(
                'type' => 'Oft\Form\Element\DateTime',
                'required' => false,
                'input_filter' => array(
                    'validators' => array(),
                    'filters' => array(),
                ), // No filters & validators for date* elements
            ),
        ),
    );

    protected function getCheckTableStatementWith($dbName, $tableName, $found)
    {
        $stmt = Mockery::mock('Doctrine\DBAL\Driver\Statement');

        $stmt->shouldReceive('execute')
            ->once()
            ->with(array(
                'schema' => $dbName,
                'table' => $tableName,
            ));

        $stmt->shouldReceive('fetch')
            ->once()
            ->andReturn(array(
                'count' => $found
            ));

        return $stmt;
    }

    protected function getInfoTableStatementWith($dbName, $tableName, $data)
    {
        $stmt = Mockery::mock('Doctrine\DBAL\Driver\Statement');

        $stmt->shouldReceive('execute')
            ->once()
            ->with(array(
                'schema' => $dbName,
                'table' => $tableName,
            ));

        $iterator = new ArrayObject($data);

        $stmt->shouldReceive('getIterator')
            ->once()
            ->andReturn($iterator);

        return $stmt;
    }

    public function testCheckTableExistsThrowsException()
    {
        $this->setExpectedException('InvalidArgumentException');

        $dbName = 'schema_test';
        $tableName = 'table_test';

        $db = Mockery::mock('Doctrine\DBAL\Connection');
        $db->shouldReceive('getDatabase')->once()->andReturn($dbName);

        $stmt = $this->getCheckTableStatementWith($dbName, $tableName, 0 /* NOT FOUND */);
        $db->shouldReceive('prepare')
            ->andReturn($stmt);

        $desc = new TableDescription($db, $tableName);
    }

    public function testInit()
    {
        $dbName = 'schema_test';
        $tableName = 'table_test';

        $db = Mockery::mock('Doctrine\DBAL\Connection');
        $db->shouldReceive('getDatabase')->once()->andReturn($dbName);

        // Check
        $stmtCheck = $this->getCheckTableStatementWith($dbName, $tableName, 1 /* FOUND */);
        $db->shouldReceive('prepare')
            ->with(TableDescription::QUERY_EXISTS)
            ->andReturn($stmtCheck);

        // Init
        $stmtInit = $this->getInfoTableStatementWith($dbName, $tableName, $this->columnsDefinition);
        $db->shouldReceive('prepare')
            ->with(TableDescription::QUERY_INFOS)
            ->andReturn($stmtInit);

        $desc = new TableDescription($db, $tableName);

        $this->assertEquals($this->descriptionResults['columns'], $desc->columns);
        $this->assertEquals($this->descriptionResults['primary'], $desc->primary);
        $this->assertEquals($this->descriptionResults['formElements'], $desc->formElements);
    }

    public function testInitNoPrimaryThrowsException()
    {
        $this->setExpectedException('DomainException');

        $dbName = 'schema_test';
        $tableName = 'table_test';

        $db = Mockery::mock('Doctrine\DBAL\Connection');
        $db->shouldReceive('getDatabase')->once()->andReturn($dbName);

        // Check
        $stmtCheck = $this->getCheckTableStatementWith($dbName, $tableName, 1 /* FOUND */);
        $db->shouldReceive('prepare')
            ->with(TableDescription::QUERY_EXISTS)
            ->andReturn($stmtCheck);

        // Init
        $columnsDefinition = $this->columnsDefinition;
        unset($columnsDefinition[0]); // remove fisrt element -> primary

        $stmtInit = $this->getInfoTableStatementWith($dbName, $tableName, $columnsDefinition);
        $db->shouldReceive('prepare')
            ->with(TableDescription::QUERY_INFOS)
            ->andReturn($stmtInit);

        $desc = new TableDescription($db, $tableName);
    }

}
