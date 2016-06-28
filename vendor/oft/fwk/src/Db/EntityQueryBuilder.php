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

namespace Oft\Db;

use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Query\QueryBuilder;
use PDO;
use RuntimeException;

class EntityQueryBuilder extends QueryBuilder
{
    protected $fetchClass;
    protected $fetchArgs;

    public function __construct(DbalConnection $connection, $fetchClass = 'Oft\Entity\BaseEntity', array $fetchArgs = array())
    {
        parent::__construct($connection);

        $this->fetchClass = $fetchClass;
        $this->fetchArgs = $fetchArgs;
    }

    public function setFetchClass($fetchClass)
    {
        $this->fetchClass = $fetchClass;
    }

    public function getFetchClass()
    {
        return $this->fetchClass;
    }

    public function setFetchArgs(array $fetchArgs)
    {
        $this->fetchArgs = $fetchArgs;
    }

    public function getFetchArgs()
    {
        return $this->fetchArgs;
    }

    public function applyOptions(array $options)
    {
        if (!isset($options['table'])) {
            throw new \RuntimeException("A table name must be specified");
        }

        $options = array_merge(
            array(
                'alias' => null,
                'columns' => array('*'),
                'sort' => array(),
                'filters' => array(),
            ),
            $options
        );

        $this->select($options['columns'])
            ->from($options['table'], $options['alias']);

        foreach ($options['sort'] as $column => $order) {
            $this->addOrderBy($column, $order);
        }

        $this->applyFilters($options['filters']);

        return $this;
    }

    public function applyFilters(array $filters)
    {
        static $first = true;

        foreach ($filters as $filter) {
            if (!isset($filter['operator'])) {
                $filter['operator'] = '=';
            }

            if (strtoupper($filter['operator']) === 'LIKE') {
                $filter['value'] = '%' . $filter['value'] . '%';
            }

            $bindName = isset($filter['bindName']) && !empty($filter['bindName'])
                ? $filter['bindName']
                : $filter['field'];

            $sqlWhere = $filter['field'] . ' ' . $filter['operator'] . ' :' . $bindName;

            if ($first) {
                $this->where($sqlWhere);
                $first = false;
            } else {
                $this->andWhere($sqlWhere);
            }

            $this->setParameter($bindName, $filter['value']);
        }

        return $this;
    }

    /**
     * Define a specific FetchMode
     *
     * @return Statement
     * @throws RuntimeException
     */
    public function execute()
    {
        $result = parent::execute();

        if (is_bool($result)) {
            return $result;
        }

        if ($this->getType() === QueryBuilder::SELECT) {
            $result->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, $this->fetchClass, $this->fetchArgs);
        }

        return $result;
    }
}
