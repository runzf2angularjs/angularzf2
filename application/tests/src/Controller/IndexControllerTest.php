<?php

namespace App\Test\Controller;

use App\Controller\IndexController;
use PHPUnit_Framework_TestCase;
use Test\Mock\ApplicationMock;

class IndexControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Méthode de création générique de controlleur.
     *
     * Utilise \Test\Mock\ApplicationMock pour créer une application factice utilisable.
     *
     * @param array $identity
     * @return IndexController
     */
    protected function getController(array $identity = array())
    {
        $controller = new IndexController();
        $controller->setApplication(ApplicationMock::factory(array(), $identity));

        return $controller;
    }

    /**
     * @covers App\Controller\IndexController::indexAction
     */
    public function testIndexActionWhenGuest()
    {
        // Préparation
        $controller = $this->getController();

        // Action
        $result = $controller->indexAction();

        // Vérification
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('username', $result);
        $this->assertSame('', $result['username']);
    }

    /**
     * @covers App\Controller\IndexController::indexAction
     */
    public function testIndexActionWhenLogged()
    {
        // Préparation
        $controller = $this->getController(array('username' => 'test'));

        // Action
        $result = $controller->indexAction();

        // Vérification
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('username', $result);
        $this->assertSame('Test', $result['username']);
    }

}
