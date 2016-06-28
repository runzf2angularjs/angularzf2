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

namespace Oft\Test\Db;

class NoDbConnectionTest extends \PHPUnit_Framework_TestCase
{
    protected $noDb;

    protected function setUp()
    {
        $this->noDb = new \Oft\Db\NoDbConnection;
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Not implemented
     */
    public function testBeginTransaction()
    {
        $this->noDb->beginTransaction();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Not implemented
     */
    public function testCommit()
    {
        $this->noDb->commit();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Not implemented
     */
    public function testErrorCode()
    {
        $this->noDb->errorCode();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Not implemented
     */
    public function testErrorInfo()
    {
        $this->noDb->errorInfo();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Not implemented
     */
    public function testExec()
    {
        $this->noDb->exec('statement');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Not implemented
     */
    public function testLastInsertId()
    {
        $this->noDb->lastInsertId();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Not implemented
     */
    public function testPrepare()
    {
        $this->noDb->prepare('some query');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Not implemented
     */
    public function testQuery()
    {
        $this->noDb->query();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Not implemented
     */
    public function testQuote()
    {
        $this->noDb->quote('some input');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Not implemented
     */
    public function testRollBack()
    {
        $this->noDb->rollBack();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Not implemented
     */
    public function testCreateQueryBuilder()
    {
        $this->noDb->createQueryBuilder();
    }

}
