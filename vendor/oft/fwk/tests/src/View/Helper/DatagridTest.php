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

namespace Oft\Test\View\Helper;

use Mockery;
use Oft\Mvc\Application;
use Oft\View\Helper\Datagrid;
use PHPUnit_Framework_TestCase;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;

class Test
{

    public function test()
    {

    }

}

class DatagridTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Datagrid
     */
    protected $datagrid;

    protected function tearDown()
    {
        \Oft\Util\Functions::setApp(null);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testNormalizeColumnsOptionsWithEmpty()
    {
        $columnsOptions = array();

        $this->datagrid = new Datagrid();

        $this->datagrid->normalizeColumnsOptions($columnsOptions);
    }

    public function testNormalizeColumnsOptionsWithString()
    {
        $expected = array(
            'test' => array(
                'name' => 'column1'
            ),
        );

        $columnsOptions = array('test' => 'column1');

        $this->datagrid = new Datagrid();

        $result = $this->datagrid->normalizeColumnsOptions($columnsOptions);

        $this->assertSame($expected, $result);
    }

    public function testNormalizeColumnsOptionsWithStringAndArray()
    {
        $expected = array(
            'test' => array(
                'name' => 'column1'
            ),
            'test1' => array(
                'name' => 'column2'
            )
        );

        $columnsOptions = array(
            'test' => 'column1',
            'test1' => array(
                'name' => 'column2'
            )
        );

        $this->datagrid = new Datagrid();

        $result = $this->datagrid->normalizeColumnsOptions($columnsOptions);

        $this->assertSame($expected, $result);
    }

    public function testGetAjaxData()
    {
        $this->datagrid = new Datagrid();

        $result = $this->datagrid->getAjaxData('id', array(), array(), array());

        $this->assertSame(array(), $result);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testNormalizeGridOptionsCallbackException()
    {
        $options = array(
            'callback' => 'test'
        );

        $this->datagrid = new Datagrid();

        $this->datagrid->normalizeGridOptions($options);
    }

    public function testNormalizeGridOptionsCallback()
    {
        $options = array(
            'callback' => array(new Test(), 'test')
        );

        $view = Mockery::mock('\Oft\View\View');
        $view->shouldReceive('smartUrl')
            ->once()
            ->andReturn('url');

        $this->datagrid = new Datagrid();
        $this->datagrid->setView($view);

        $result = $this->datagrid->normalizeGridOptions($options);
        $this->assertSame($result['callback'], $options['callback']);
    }

    public function testNormalizeGridOptionsEmpty()
    {
        $exprected = array(
            'pageRange' => 10,
            'page' => 1,
            'itemPerPage' => 10,
            'actions' => array(),
            'orderLink' => 'url',
            'linkOn' => array()
        );

        $options = array();

        $view = Mockery::mock('\Oft\View\View');
        $view->shouldReceive('smartUrl')
            ->once()
            ->andReturn('url');

        $this->datagrid = new Datagrid();
        $this->datagrid->setView($view);

        $result = $this->datagrid->normalizeGridOptions($options);

        $this->assertSame($exprected, $result);
    }

    public function testNormalizeGridOptionsPagination()
    {
        $exprected = array(
            'pageRange' => 15,
            'page' => 11,
            'itemPerPage' => 13,
            'actions' => array('test'),
            'orderLink' => 'url',
            'linkOn' => array()
        );

        $options = array(
            'pageRange' => 15,
            'page' => 11,
            'itemPerPage' => 13,
            'actions' => array('test'),
        );

        $view = Mockery::mock('\Oft\View\View');
        $view->shouldReceive('smartUrl')
            ->once()
            ->andReturn('url');

        $this->datagrid = new Datagrid();
        $this->datagrid->setView($view);

        $result = $this->datagrid->normalizeGridOptions($options);

        $this->assertSame($exprected, $result);
    }

    public function testNormalizeGridOptionLink()
    {
        $exprected = array(
            'action' => 'action1',
            'controller' => 'controller1',
            'module' => 'module1',
            'params' => array(),
            'name' => 'test'
        );

        $options = array(
            'link' => array(
                'action' => 'action1',
                'controller' => 'controller1',
                'module' => 'module1',
                'params' => array(),
                'name' => 'test'
            )
        );

        $view = Mockery::mock('\Oft\View\View');
        $view->shouldReceive('smartUrl')
            ->andReturn('url');

        $this->datagrid = new Datagrid();
        $this->datagrid->setView($view);

        $result = $this->datagrid->normalizeGridOptions($options);

        $this->assertSame($exprected, $result['link']);
    }

    public function testNormalizeGridOptionLinkOnString()
    {
        $exprected = array(
            'action1'
        );

        $options = array(
            'linkOn' => 'action1'
        );

        $view = Mockery::mock('\Oft\View\View');
        $view->shouldReceive('smartUrl')
            ->once()
            ->andReturn('url');

        $this->datagrid = new Datagrid();
        $this->datagrid->setView($view);

        $result = $this->datagrid->normalizeGridOptions($options);

        $this->assertSame($exprected, $result['linkOn']);
    }

    public function testNormalizeGridOptionLinkOnArray()
    {
        $exprected = array(
            'action1'
        );

        $options = array(
            'linkOn' => array(
                'action1'
            )
        );

        $view = Mockery::mock('\Oft\View\View');
        $view->shouldReceive('smartUrl')
            ->once()
            ->andReturn('url');

        $this->datagrid = new Datagrid();
        $this->datagrid->setView($view);

        $result = $this->datagrid->normalizeGridOptions($options);

        $this->assertSame($exprected, $result['linkOn']);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testNormalizeGridOptionLinkOnException()
    {
        $options = array(
            'linkOn' => 1
        );

        $view = Mockery::mock('\Oft\View\View');
        $view->shouldReceive('smartUrl')
            ->once()
            ->andReturn('url');

        $this->datagrid = new Datagrid();
        $this->datagrid->setView($view);

        $this->datagrid->normalizeGridOptions($options);
    }

    public function testGetPaginator()
    {
        $view = Mockery::mock('\Oft\View\View');
        $view->shouldReceive('smartUrl')
            ->once()
            ->andReturn('url');

        $this->datagrid = new Datagrid();
        $this->datagrid->setView($view);

        $options = $this->datagrid->normalizeGridOptions(array());

        $result = $this->datagrid->getPaginator(array(), $options);
        $this->assertInstanceOf('\Zend\Paginator\Paginator', $result);
    }

    public function testGetPaginatorWithPaginatorParam()
    {
        $this->datagrid = new Datagrid();

        $paginator = new Paginator(new ArrayAdapter(array()));

        $options = array(
            'pageRange' => 1,
            'itemPerPage' => 10,
            'page' => 1,
        );

        $result = $this->datagrid->getPaginator($paginator, $options);
        $this->assertInstanceOf('\Zend\Paginator\Paginator', $result);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetActionColumnException()
    {
        $this->datagrid = new Datagrid();

        $this->datagrid->getActionColumn(array(), 'id', 1);
    }

    public function testGetActionColumnContent()
    {
        $this->datagrid = new Datagrid();

        $result = $this->datagrid->getActionColumn(array('content' => 'test'), 'id', 1);

        $this->assertEquals('test', $result);
    }

    public function testGetActionColumnImage()
    {
        $expected = '<img alt="altimg" title="altimg" src="/test.jpg">';

        $view = Mockery::mock('\Oft\View\View');
        $view->shouldReceive('basepath')
            ->once()
            ->andReturn('/');

        $config['escaper']['encoding'] = 'UTF-8';

        $app = new Application($config);

        \Oft\Util\Functions::setApp($app);

        $this->datagrid = new Datagrid();
        $this->datagrid->setView($view);

        $result = $this->datagrid->getActionColumn(array('image' => 'test.jpg', 'alt' => 'altimg'), 'id', 1);

        $this->assertEquals($expected, $result);
    }

    public function testGetActionColumnFile()
    {
        $expected = '<img alt="" title="" src="/test.jpg">';

        $asset = Mockery::mock('\Oft\View\Helper\Assets');
        $asset->shouldReceive('file')
            ->once()
            ->with('test.jpg')
            ->andReturn('/test.jpg');

        $view = Mockery::mock('\Oft\View\View');
        $view->shouldReceive('assets')
            ->once()
            ->andReturn($asset);

        $this->datagrid = new Datagrid();
        $this->datagrid->setView($view);

        $result = $this->datagrid->getActionColumn(array('file' => 'test.jpg'), 'id', 1);

        $this->assertEquals($expected, $result);
    }

    public function testGetActionColumnLinkTitle()
    {
        $expected = '<a title="titre" href="/link?id=1">test</a>';

        $view = Mockery::mock('\Oft\View\View');
        $view->shouldReceive('smartUrl')
            ->once()
            ->andReturn('/link');

        $this->datagrid = new Datagrid();
        $this->datagrid->setView($view);

        $result = $this->datagrid->getActionColumn(array(
            'link' => array(
                'action' => 'act',
                'controller' => 'ctrl',
                'module' => 'mod',
            ),
            'title' => 'titre',
            'content' => 'test'
            ), 'id', array('id' => 1));

        $this->assertEquals($expected, $result);
    }

    public function testGetActionColumnLinkAlt()
    {
        $expected = '<a title="titre" href="/link?id=1">test</a>';

        $view = Mockery::mock('\Oft\View\View');
        $view->shouldReceive('smartUrl')
            ->once()
            ->andReturn('/link');

        $this->datagrid = new Datagrid();
        $this->datagrid->setView($view);

        $result = $this->datagrid->getActionColumn(array(
            'link' => array(
                'action' => 'act',
                'controller' => 'ctrl',
                'module' => 'mod',
            ),
            'alt' => 'titre',
            'content' => 'test'
            ), 'id', array('id' => 1));

        $this->assertEquals($expected, $result);
    }

    protected function initInvoke()
    {
        $translator = new \Zend\I18n\Translator\Translator();

        $view = Mockery::mock('\Oft\View\View');
        $view->shouldReceive('smartUrl')
            ->andReturn('/link');
        $view->shouldReceive('paginationControl')
            ->andReturn('pagination');

        $this->datagrid = new Datagrid();
        $this->datagrid->setTranslator($translator);
        $this->datagrid->setView($view);
    }

    public function testInvokeEmptyArray()
    {
        $expected = '<table class="table table-bordered table-striped table-condensed">'
            . '<thead><tr><th class="text-center">Id</th></tr></thead>'
            . '<tbody></tbody>'
            . '</table>';

        $columnOptions = array(
            'id' => 'Id'
        );

        $option = array();

        $this->initInvoke();

        $result = $this->datagrid->__invoke('id', array(), $columnOptions, $option);

        $this->assertEquals($expected, $result);
    }

    public function testInvokeVisible()
    {
        $expected = '<table class="table table-bordered table-striped table-condensed">'
            . '<thead><tr></tr></thead>'
            . '<tbody></tbody>'
            . '</table>';

        $columnOptions = array(
            'id' => array('visible' => false)
        );

        $option = array();

        $this->initInvoke();

        $result = $this->datagrid->__invoke('id', array(), $columnOptions, $option);

        $this->assertEquals($expected, $result);
    }

    public function testInvokeWidth()
    {
        $expected = '<table class="table table-bordered table-striped table-condensed">'
            . '<thead><tr><th class="text-center" style="width:10px">id</th></tr></thead>'
            . '<tbody></tbody>'
            . '</table>';

        $columnOptions = array(
            'id' => array('width' => '10px'));

        $option = array();

        $this->initInvoke();

        $result = $this->datagrid->__invoke('id', array(), $columnOptions, $option);

        $this->assertEquals($expected, $result);
    }

    public function testInvokeSortable()
    {
        $expected = '<table class="table table-bordered table-striped table-condensed">'
            . '<thead><tr><th class="text-center">'
            . '<a href="/link?sort=id&order=asc" title="No sorting">id <span aria-hidden="true" class="glyphicon glyphicon-sort"></span></a>'
            . '</th></tr></thead>'
            . '<tbody></tbody>'
            . '</table>';

        $columnOptions = array(
            'id' => array('sortable' => true)
        );

        $option = array();

        $this->initInvoke();

        $result = $this->datagrid->__invoke('id', array(), $columnOptions, $option);

        $this->assertEquals($expected, $result);
    }
    
        public function testInvokeSortableWithParamsInOrderLink()
    {
        $expected = '<table class="table table-bordered table-striped table-condensed">'
            . '<thead><tr><th class="text-center">'
            . '<a href="test?param=value&sort=id&order=asc" title="No sorting">id <span aria-hidden="true" class="glyphicon glyphicon-sort"></span></a>'
            . '</th></tr></thead>'
            . '<tbody></tbody>'
            . '</table>';

        $columnOptions = array(
            'id' => array('sortable' => true)
        );

        $option = array(
            'orderLink' => 'test?param=value'
        );

        $this->initInvoke();

        $result = $this->datagrid->__invoke('id', array(), $columnOptions, $option);

        $this->assertEquals($expected, $result);
    }

    public function testInvokeSortableAsc()
    {
        $expected = '<table class="table table-bordered table-striped table-condensed">'
            . '<thead><tr><th class="text-center">'
            . '<a href="/link?sort=id&order=desc" title="Sorting ascendant">id <span aria-hidden="true" class="glyphicon glyphicon-arrow-up"></span></a>'
            . '</th></tr></thead>'
            . '<tbody></tbody>'
            . '</table>';

        $columnOptions = array(
            'id' => array('sortable' => true)
        );

        $option = array(
            'sort' => 'id'
        );

        $this->initInvoke();

        $result = $this->datagrid->__invoke('id', array(), $columnOptions, $option);

        $this->assertEquals($expected, $result);
    }

    public function testInvokeSortableDesc()
    {
        $expected = '<table class="table table-bordered table-striped table-condensed">'
            . '<thead><tr><th class="text-center">'
            . '<a href="/link?sort=id&order=asc" title="Sorting descendant">id <span aria-hidden="true" class="glyphicon glyphicon-arrow-down"></span></a>'
            . '</th></tr></thead>'
            . '<tbody></tbody>'
            . '</table>';

        $columnOptions = array(
            'id' => array('sortable' => true)
        );

        $option = array(
            'sort' => 'id',
            'order' => 'desc'
        );

        $this->initInvoke();

        $result = $this->datagrid->__invoke('id', array(), $columnOptions, $option);

        $this->assertEquals($expected, $result);
    }

    public function testInvokeActions()
    {
        $expected = '<table class="table table-bordered table-striped table-condensed">'
            . '<thead><tr>'
            . '<th class="text-center">id</th>'
            . '<th class="text-center" style="width:10px">Actions</th><'
            . '/tr></thead>'
            . '<tbody><tr>'
            . '<td class="text-right">1</td>'
            . '<td class="text-center">action</td>'
            . '</tr></tbody>'
            . '</table>';

        $columnOptions = array(
            'id' => array('align' => 'right'),
            'actions' => array('width' => '10px')
        );

        $option = array(
            'actions' => array(
                'test' => array(
                    'content' => 'action'
                )
            )
        );

        $this->initInvoke();

        $result = $this->datagrid->__invoke('id', array(array('id' => 1)), $columnOptions, $option);

        $this->assertEquals($expected, $result);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testInvokeBadDataException()
    {
        $columnOptions = array(
            'id' => array()
        );

        $option = array();

        $this->initInvoke();

        $this->datagrid->__invoke('id', array(array('id1' => 1)), $columnOptions, $option);
    }

    public function testInvokeLinkOn()
    {
        $expected = '<table class="table table-bordered table-striped table-condensed">'
            . '<thead><tr>'
            . '<th class="text-center">id</th>'
            . '</tr></thead>'
            . '<tbody><tr>'
            . '<td class="text-left"><a href="/link?id=1">1</a></td>'
            . '</tr></tbody>'
            . '</table>';

        $columnOptions = array(
            'id' => array('align' => 'left'),
        );

        $option = array(
            'linkOn' => 'id',
            'link' => array()
        );

        $this->initInvoke();

        $result = $this->datagrid->__invoke('id', array(array('id' => 1)), $columnOptions, $option);

        $this->assertEquals($expected, $result);
    }

    public function testGetLinkQueryStringWithMultipleKeys()
    {
        $dataGrid = new Datagrid();

        $result = $dataGrid->getLinkQueryString(array('id1', 'id2'), array('id1' => 1, 'id2' => 3));

        $this->assertSame('id1=1&id2=3', $result);
    }
    
    public function testInvokeSortLinkPagination()
    {
        $expected = '<table class="table table-bordered table-striped table-condensed">'
            . '<thead><tr>'
            . '<th class="text-center"><a href="test?param=value&sort=id&order=asc" title="Sorting descendant">id '
            . '<span aria-hidden="true" class="glyphicon glyphicon-arrow-down"></span></a>'
            . '</th>'
            . '</tr></thead>'
            . '<tbody><tr>'
            . '<td class="text-center">1</td>'
            . '</tr></tbody>'
            . '<tfoot><tr><td colspan="1" align="center">pagination</td></tr></tfoot>'
            . '</table>';

        $columnOptions = array(
            'id' => array('sortable' => true)
        );

        $option = array(
            'sort' => 'id',
            'order' => 'desc',
            'itemPerPage' => '1',
            'pageRange' => '10',
            'page' => '1',
            'orderLink' => 'test?param=value',
        );

        $translator = new \Zend\I18n\Translator\Translator();

        

        $this->datagrid = new Datagrid();
        
        $data = array(array('id' => 1), array('id' => 2));
                
        $view = Mockery::mock('\Oft\View\View');
        $view->shouldReceive('smartUrl')
            ->andReturn('/link');
        $view->shouldReceive('paginationControl')
            ->withArgs(array(Mockery::type("Zend\Paginator\Paginator"), "Sliding", "oft/partials/sliding", array('url'=>'test?param=value&sort=id&order=desc')))
            ->andReturn('pagination');
        
        $this->datagrid->setTranslator($translator);
        $this->datagrid->setView($view);

        $result = $this->datagrid->__invoke('id', $data , $columnOptions, $option);

        $this->assertEquals($expected, $result);
    }
}
