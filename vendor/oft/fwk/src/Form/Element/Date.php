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

namespace Oft\Form\Element;

/**
 * Elément de formulaire de type "date"
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class Date extends DateTime
{
    
    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes = array(
        'type' => 'text',
        'dateFormat' => 'short',
        'timeFormat' => 'none',
        'dateSqlFormat' => 'sql',
        'timeSqlFormat' => 'none',
    );
    
}
