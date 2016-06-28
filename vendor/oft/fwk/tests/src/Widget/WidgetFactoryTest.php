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

namespace Oft\Test\Widget;

class TestWidget
{
    public function __invoke($p1, $p2)
    {
        return $p1 . ' ' . $p2;
    }

}

class WidgetFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function getWidgetFactory($widgets = array())
    {
        $app = new \Oft\Mvc\Application(array(
            'widgets' => $widgets,
        ));
        return new \Oft\Widget\WidgetFactory($app);
    }

    public function testCallWidgetInvoke()
    {
        $widget =  $this->getWidgetFactory(array(
            'w' => 'Oft\Test\Widget\TestWidget'
        ))->get('w');

        $result = $widget(1, 3);

        $this->assertSame('1 3', $result);
    }

}
