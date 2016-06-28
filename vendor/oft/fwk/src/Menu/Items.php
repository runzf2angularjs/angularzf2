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

namespace Oft\Menu;

class Items extends \ArrayObject
{
    protected static $defaultItem = array(
        'name' => '',
        'route' => array(),
        'align' => 'left',
        'position' => null,
        'submenu' => null,
        'url' => '',
        'target' => '',
    );

    protected $currentPosition = 1;

    public function __construct(array $items = array())
    {
        parent::__construct(array());
        $this->addItems($items);
    }

    public function addItems(array $items)
    {
        foreach ($items as $name => $item) {
            $this->addItem($item, $name);
        }

        return $this;
    }

    public function addItem(array $item, $name = null)
    {
        if (empty($name)) {
            if (!isset($item['name'])) {
                throw new \RuntimeException("Unable to create an entry with no name");
            }
            $name = $item['name'];
        } else if (empty($item['name'])) {
            $item['name'] = ucwords($name);
        }

        if (!isset($item['position'])) {
            $item['position'] = $this->currentPosition ++;
        }
        
        $this[$name] = array_merge(
            self::$defaultItem, $item
        );

        return $this;
    }
}
