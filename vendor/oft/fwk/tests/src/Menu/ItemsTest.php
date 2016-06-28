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

namespace Oft\Test\Menu;

use Oft\Menu\Items;
use PHPUnit_Framework_TestCase;

class ItemsTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Items
     */
    protected $items;

    protected function setUp()
    {
        $this->items = new Items;
    }

    /**
     * @covers Oft\Menu\Items::getArrayCopy
     */
    public function testGetItems()
    {
        $items = $this->items->getArrayCopy();

        $this->assertInternalType('array', $items);
    }
    
    /**
     * @covers Oft\Menu\Items::__construct
     */
    public function testConstruct()
    {
        $this->items = new Items(array(
            array('name' => 'test',)
        ));
        
        $items = $this->items->getArrayCopy();

        $this->assertArrayHasKey('test', $items);
        $this->assertArrayHasKey('name', $items['test']);
        $this->assertSame('test', $items['test']['name']);
    }

    /**
     * @covers Oft\Menu\Items::addItems
     */
    public function testAddItems()
    {
        $this->items->addItems(array(
            array('name' => 'test',)
        ));

        $items = $this->items->getArrayCopy();

        $this->assertArrayHasKey('test', $items);
        $this->assertArrayHasKey('name', $items['test']);
        $this->assertSame('test', $items['test']['name']);
    }

    /**
     * @covers Oft\Menu\Items::addItem
     */
    public function testAddItemExceptionIfNotArray()
    {
        $this->setExpectedException('PHPUnit_Framework_Exception', 'Argument 1 passed to Oft\Menu\Items::addItem() must be of the type array');
        $this->items->addItem('menu?');
    }

    /**
     * @covers Oft\Menu\Items::addItem
     */
    public function testAddItemWithArrayAndNoName()
    {
        $this->items->addItem(array('name' => 'menu'));
        
        $items = $this->items->getArrayCopy();

        $this->assertArrayHasKey('menu', $items);
        $this->assertArrayHasKey('name', $items['menu']);
        $this->assertSame('menu', $items['menu']['name']);
    }

    /**
     * @covers Oft\Menu\Items::addItem
     */
    public function testAddItemWithArrayAndNoNameThrowException()
    {
        $this->setExpectedException('RuntimeException', 'Unable to create an entry with no name');

        $this->items->addItem(array('position' => 3));
    }


    /**
     * @covers Oft\Menu\Items::addItem
     */
    public function testAddItemWithArrayAndName()
    {
        $this->items->addItem(array('position' => 3), 'menu');

        $items = $this->items->getArrayCopy();

        $this->assertArrayHasKey('menu', $items);
        $this->assertArrayHasKey('position', $items['menu']);
        $this->assertSame(3, $items['menu']['position']);
    }
}
