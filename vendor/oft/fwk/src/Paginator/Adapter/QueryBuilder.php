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

namespace Oft\Paginator\Adapter;

use Doctrine\DBAL\Query\QueryBuilder as DbalQueryBuilder;
use PDO;
use Zend\Paginator\Adapter\AdapterInterface;

class QueryBuilder implements AdapterInterface
{

    /**
     * Objet de construction d'une requête de type SELECT COUNT()
     *
     * @var DbalQueryBuilder
     */
    protected $countQuery;

    /**
     * Nombre de ligne du résultat
     *
     * @var int
     */
    protected $count;

    /**
     * Objet de construction de la requête
     *
     * @var DbalQueryBuilder
     */
    protected $queryBuilder;

    /**
     * Classe modèle à laquelle le jeu de résultat sera lié
     *
     * @var string
     */
    protected $fetchClass;

    /**
     * Initialise l'adaptateur
     *
     * La requête doit être de type SELECT
     *
     * @param DbalQueryBuilder $queryBuilder Objet de construction de la requête
     * @param string $fetchClass Classe modèle
     * @throws \RuntimeException
     * @return self
     */
    public function __construct(DbalQueryBuilder $queryBuilder, $fetchClass = 'Oft\Entity\BaseEntity')
    {
        if ($queryBuilder->getType() !== DbalQueryBuilder::SELECT) {
            throw new \RuntimeException("Impossible d'utiliser une requête qui ne soit pas un SELECT");
        }

        $this->queryBuilder = $queryBuilder;
        $this->fetchClass = $fetchClass;
    }

    /**
     * Définit l'objet de construction d'une requête de type SELECT COUNT()
     *
     * Réinitialise le nombre de ligne résultat
     *
     * @param DbalQueryBuilder $countQuery Requête de type SELECT COUNT()
     * @throws \RuntimeException
     * @return void
     */
    public function setCountQuery(DbalQueryBuilder $countQuery)
    {
        if ($countQuery->getType() !== DbalQueryBuilder::SELECT) {
            throw new \RuntimeException("Impossible d'utiliser une requête qui ne soit pas un SELECT");
        }

        $this->count = null;
        $this->countQuery = $countQuery;
    }

    /**
     * Construit, si nécessaire, et retourne l'objet de
     * construction de la requête de type SELECT COUNT()
     *
     * @return DbalQueryBuilder
     */
    public function getCountQuery()
    {
        if (! $this->countQuery) {
            $this->countQuery = clone $this->queryBuilder;
            $this->countQuery
                ->resetQueryPart('select')
                ->resetQueryPart('groupBy')
                ->resetQueryPart('having')
                ->resetQueryPart('orderBy')
                ->select('count(*)');
        }

        return $this->countQuery;
    }

    /**
     * Retourne une collection de résultats
     *
     * @param int $offset Offset de la page
     * @param int $itemCountPerPage Nombre de résultats par page
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $query = $this->queryBuilder
            ->setFirstResult($offset)
            ->setMaxResults($itemCountPerPage);

        $results = $query->execute();

        if ($this->fetchClass) {
            $results->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, $this->fetchClass);
        }

        return $results;
    }

    /**
     * Retourne le nombre de résultat pour la requête de type SELECT COUNT()
     *
     * @return int
     */
    public function count()
    {
        if ($this->count === null) {
            $this->count = $this->getCountQuery()->execute()->fetchColumn(0);
        }

        return $this->count;
    }

}
