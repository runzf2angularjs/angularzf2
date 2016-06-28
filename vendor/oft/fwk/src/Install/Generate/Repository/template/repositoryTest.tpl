<?php

/* @var $this Oft\View\View */

echo "<?php\n";
?>

namespace <?=$namespace?>;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Mockery;
use Oft\Entity\BaseEntity;
use Oft\Mvc\Application;
use <?=$fullClassName?>;
use PHPUnit_Framework_TestCase;

class <?=$testClassName?> extends PHPUnit_Framework_TestCase
{

    public function testConstruct()
    {
        $db = Mockery::mock('Doctrine\DBAL\Connection');

        $repository = $this->getRepositoryWith($db);

        $this->assertInstanceOf('<?=$fullClassName?>', $repository);
    }

    public function testMetadata()
    {
        $table = <?=$className?>::$table;
        $primary = <?=$className?>::$primary;
        $metadata = <?=$className?>::$metadata;

        $this->assertTrue(!empty($table) && is_string($table));
        $this->assertTrue(!empty($table) && is_array($metadata));
        $this->assertTrue(!empty($table) && is_array($primary));
    }

    public function testGetNew()
    {
        $db = Mockery::mock('Doctrine\DBAL\Connection');
        $repository = $this->getRepositoryWith($db);

        $entity = $repository->getNew();

        $this->assertInstanceOf('Oft\Entity\BaseEntity', $entity);
    }

    public function testGetNewWithUnknownKey()
    {
        $db = Mockery::mock('Doctrine\DBAL\Connection');
        $repository = $this->getRepositoryWith($db);

        $entity = $repository->getNew(array('<?=$columns[0]?>_unknown' => 'value'));

        $this->assertTrue(!isset($entity-><?=$columns[0]?>_unknown));
    }

    public function testGetQueryBuilderNoFilters()
    {
        $sort = '<?=$primary[0]?>';
        $order = 'asc';
        $filters = array();

        $db = Mockery::mock('Doctrine\DBAL\Connection');
        $repository = $this->getRepositoryWith($db);

        $queryBuilder = $repository->getQueryBuilder(array(
            'filters' => $filters,
            'sort' => array($sort => $order),
        ));

        $this->assertEquals(
            "SELECT <?= implode(', ', $columns) ?> FROM <?= $tableName ?> ORDER BY <?= $primary[0] ?> asc",
            $queryBuilder->getSQL()
        );
    }

    public function testGetQueryBuilderWithFilters()
    {
        $sort = '<?=$primary[0]?>';
        $order = 'asc';
        $filters = array(
            array( // Test : 0 n'est pas une valeur vide et est traité
                'field' => '<?=$columns[0]?>',
                'value' => '0', // 0
                'operator' => '<>'
            ),
            array( // Test : '=' est l'opérateur par défaut
                'field' => '<?=$columns[0]?>',
                'value' => '<?=$columns[0]?>_equal',
                // 'operator' => '=',
            ),
            array( // Test : opérateur LIKE
                'field' => '<?=$columns[0]?>',
                'value' => '<?=$columns[0]?>_like',
                'operator' => 'LIKE'
            ),
        );

        $db = Mockery::mock('Doctrine\DBAL\Connection');
        $repository = $this->getRepositoryWith($db);

        $queryBuilder = $repository->getQueryBuilder(array(
            'filters' => $filters,
            'sort' => array($sort => $order),
        ));

        $this->assertEquals(
                "SELECT <?= implode(', ', $columns) ?> "
            .   "FROM <?= $tableName ?> "
            .   "WHERE (<?=$columns[0]?> <> :<?=$columns[0]?>) AND (<?=$columns[0]?> = :<?=$columns[0]?>) AND (<?=$columns[0]?> LIKE :<?=$columns[0]?>) ORDER BY <?=$primary[0]?> asc",
            $queryBuilder->getSQL()
        );
    }

    public function testGetById()
    {
        $entity = new \Oft\Entity\BaseEntity(array(<?php
            foreach ($columns as $columnName) :
                echo
                    "\n" .
                    '            \'' . $columnName . '\'' .
                    ' => \'' . $columnName . '_value\',';
            endforeach;
            echo "\n";
        ?>
        ));

        $statement = Mockery::mock('Doctrine\DBAL\Statement');
        $statement->shouldReceive('setFetchMode')->once()->andReturnNull();
        $statement->shouldReceive('fetch')->once()->andReturn($entity);

        $db = Mockery::mock('Doctrine\DBAL\Connection');
        $db->shouldReceive('executeQuery')->once()->andReturn($statement);

        $repository = $this->getRepositoryWith($db);

        $expectedEntity = $repository->getById(<?php
            $args = '';
            foreach ($primary as $column) :
                $args .= '\'value\', ';
            endforeach;
            echo substr($args, 0, -2);
        ?>);

        <?php foreach ($columns as $columnName) : ?>
$this->assertEquals($expectedEntity-><?=$columnName?>, $entity-><?=$columnName?>);
        <?php endforeach; ?>
        <?= "\n" ?>
    }

    public function testGetByIdThrowException()
    {
        $data = false;

        $this->setExpectedException("DomainException");

        $statement = Mockery::mock('Doctrine\DBAL\Statement');
        $statement->shouldReceive('setFetchMode')->once()->andReturnNull();
        $statement->shouldReceive('fetch')->once()->andReturn($data);

        $db = Mockery::mock('Doctrine\DBAL\Connection');
        $db->shouldReceive('executeQuery')->once()->andReturn($statement);

        $repository = $this->getRepositoryWith($db);

        $entity = $repository->getById(<?php
            $args = '';
            foreach ($primary as $column) :
                $args .= '\'value\', ';
            endforeach;
            echo substr($args, 0, -2);
        ?>);
    }

    public function testGetPaginator()
    {
        $filters = array();

        $db = Mockery::mock('Doctrine\DBAL\Connection');
        $repository = $this->getRepositoryWith($db);

        $paginator = $repository->getPaginator($filters);

        $this->assertInstanceOf('Zend\Paginator\Paginator', $paginator);
    }

    public function testUpdate()
    {
        $entity = new \Oft\Entity\BaseEntity(array(<?php
            $lastColumnName = '';
            foreach ($columns as $columnName) :
                echo
                    "\n" .
                    '            \'' . $columnName . '\'' .
                    ' => \'' . $columnName . '_test\',';
                if (! in_array($columnName, $primary)) :
                    $lastColumnName = $columnName;
                endif;
            endforeach;
            echo "\n";
        ?>
        ), true); // Strict mode

        $entity-><?=$lastColumnName?> = '<?=$lastColumnName?>_new_test';

        $db = Mockery::mock('Doctrine\DBAL\Connection');
        $db->shouldReceive('update')
            ->once()
            ->withArgs(array(
                '<?=$tableName?>',
                array( // data (no ID)<?php
                    foreach ($columns as $columnName) :
                        if (\in_array($columnName, $primary)) continue;
                        if ($columnName != $lastColumnName) continue;
                        $value = '\'' . $columnName . '_new_test\'';
                        echo
                            "\n" .
                            '                   \'' . $columnName . '\'' .
                            ' => ' . $value . ',';
                    endforeach;
                    echo "\n";
                ?>
                ),
                array( // constraints<?php
                    foreach ($primary as $column) :
                        echo
                            "\n" .
                            '                   \'' . $column . '\'' .
                            ' => \'' . $column . '_test\',';
                    endforeach;
                    echo "\n";
                ?>
                ),
            ));

        $repository = $this->getRepositoryWith($db);

        $repository->update($entity);
    }

    public function testUpdateNotExecWithNoUpdatedData()
    {
        $entity = new \Oft\Entity\BaseEntity(array(<?php
            $lastColumnName = '';
            foreach ($columns as $columnName) :
                echo
                    "\n" .
                    '            \'' . $columnName . '\'' .
                    ' => \'' . $columnName . '_test\',';
                $lastColumnName = $columnName;
            endforeach;
            echo "\n";
        ?>
        ), true); // Strict mode

        // Pas de modification de $entity

        $db = Mockery::mock('Doctrine\DBAL\Connection');
        $db->shouldNotReceive('update');

        $repository = $this->getRepositoryWith($db);

        $repository->update($entity);
    }

    public function testInsert()
    {
        $entity = new BaseEntity(array(<?php
            foreach ($columns as $columnName) :
                echo
                    "\n" .
                    '            \'' . $columnName . '\'' .
                    ' => \'' . $columnName . '_value\',';
            endforeach;
            echo "\n";
        ?>
        ));

        $db = Mockery::mock('Doctrine\DBAL\Connection');
        $db->shouldReceive('insert')
            ->once()
            ->withArgs(array(
                '<?=$tableName?>',
                array( // data (no ID)<?php
                    foreach ($columns as $columnName) :
                        if (\in_array($columnName, $primary)) continue;
                        echo
                            "\n" .
                            '                   \'' . $columnName . '\'' .
                            ' => \'' . $columnName . '_value\',';
                    endforeach;
                    echo "\n";
                ?>
                ),
            ));

        $repository = $this->getRepositoryWith($db);

        $repository->insert($entity);
    }

    public function testDelete()
    {
        $entity = new BaseEntity(array(<?php
            foreach ($columns as $columnName) :
                echo
                    "\n" .
                    '            \'' . $columnName . '\'' .
                    ' => \'' . $columnName . '_value\',';
            endforeach;
            echo "\n";
        ?>
        ));

        $db = Mockery::mock('Doctrine\DBAL\Connection');
        $db->shouldReceive('delete')
            ->once()
            ->withArgs(array(
                '<?=$tableName?>',
                array( // constraints<?php
                    foreach ($primary as $column) :
                        echo
                            "\n" .
                            '                   \'' . $column . '\'' .
                            ' => \'' . $column . '_value\',';
                    endforeach;
                    echo "\n";
                ?>
                ),
            ));

        $repository = $this->getRepositoryWith($db);

        $repository->delete($entity);
    }

    /**
     * @return QueryBuilder
     */
    protected function getSimpleQueryBuilder()
    {
        $queryBuilder = Mockery::mock('Doctrine\DBAL\Query\QueryBuilder');
        $queryBuilder->shouldReceive('select')
            ->with(array(<?php
            foreach ($columns as $columnName) :
                echo
                    "\n" .
                    '                \'' . $columnName . '\',';
            endforeach;
            echo "\n";
        ?>
            ))
            ->once()
            ->andReturnSelf();

        $queryBuilder->shouldReceive('from')
            ->with('<?=$tableName?>')
            ->once()
            ->andReturnSelf();

        return $queryBuilder;
    }

    /**
     * @param Connection $db
     * @return <?=$className?>
     */
    protected function getRepositoryWith($db)
    {
        $app = new Application();
        $app->setService('Db', $db);

        $repository = new <?=$className?>($app);

        return $repository;
    }

}
