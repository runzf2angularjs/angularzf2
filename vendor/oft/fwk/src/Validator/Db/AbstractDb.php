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

namespace Oft\Validator\Db;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use InvalidArgumentException;
use Oft\Util\Functions;
use Zend\Validator\AbstractValidator;

abstract class AbstractDb extends AbstractValidator
{

    /**
     * Error constants
     */
    const ERROR_NO_RECORD_FOUND = 'noRecordFound';
    const ERROR_RECORD_FOUND    = 'recordFound';

    /**
     * @var array Message templates
     */
    protected $messageTemplates = array(
        self::ERROR_NO_RECORD_FOUND => "No record matching the input was found",
        self::ERROR_RECORD_FOUND    => "A record matching the input was found",
    );

    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var string
     */
    protected $table = '';

    /**
     * @var string
     */
    protected $field = '';

    /**
     * @var mixed
     */
    protected $exclude = null;

    /**
     * Configuration de base pour les validateurs Oft\Validator\Db
     *
     * Définir $exclude permet d'exclure un enregistrement au cours de la validation
     * $exclude peut-être une chaîne (contenant une clause WHERE) ou un tableau définissant une clef 'field' et une clef 'value'
     *
     * Les options suivantes peuvent être définies :
     * 'table'   => Table cible
     * 'field'   => Champ cible
     * 'exclude' => Règle d'exclusion complémentaire (optionnel)
     *
     * @param array $options Options
     * @throws InvalidArgumentException
     */
    public function __construct(array $options = array())
    {
        if (!array_key_exists('table', $options)) {
            throw new InvalidArgumentException('Table option is missing');
        }

        if (!array_key_exists('field', $options)) {
            throw new InvalidArgumentException('Field option is missing');
        }

        if (array_key_exists('exclude', $options)) {
            $this->setExclude($options['exclude']);
        }

        $this->setField($options['field']);
        $this->setTable($options['table']);
    }

    /**
     *
     * @param  QueryBuilder $queryBuilder
     * @return self
     */
    public function setQueryBuilder(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
        return $this;
    }

    /**
     *
     * @param string|array $exclude
     * @return self
     */
    public function setExclude($exclude)
    {
        $this->exclude = $exclude;
        $this->queryBuilder = null;
        return $this;
    }

    /**
     *
     * @param string $field
     * @return self
     */
    public function setField($field)
    {
        $this->field  = (string) $field;
        $this->queryBuilder = null;
        return $this;
    }

    /**
     *
     * @param string $table
     * @return self
     */
    public function setTable($table)
    {
        $this->table  = (string) $table;
        $this->queryBuilder = null;
        return $this;
    }

    /**
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        if ($this->queryBuilder instanceof QueryBuilder) {
            return $this->queryBuilder;
        }

        $db = $this->getConnection();
        $qb = $db->createQueryBuilder();

        $qb->select(array($this->field))
            ->from($this->table)
            ->where($this->field . ' = :value');

        if ($this->exclude !== null) {
            if (is_array($this->exclude)) {
                $qb->where(
                    $qb->expr()->neq(
                        $this->exclude['field'],
                        $db->quote($this->exclude['value'])
                    )
                );
            } else {
                $qb->where($this->exclude);
            }
        }

        $this->queryBuilder = $qb;

        return $this->queryBuilder;
    }

    /**
     * 
     * @param string
     * @return array
     */
    protected function query($value)
    {
        $qb = $this->getQueryBuilder()
            ->setParameter(':value', $value);

        return $qb->execute()->fetch();
    }

    /**
     *
     * @return Connection
     */
    protected function getConnection()
    {
        return Functions::getApp()->db;
    }

}
