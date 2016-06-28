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

namespace Oft\Validator;

use Mockery;
use Oft\Validator\Translator;
use PHPUnit_Framework_TestCase;

class TranslatorTest extends PHPUnit_Framework_TestCase
{
    
    public function testTranslate()
    {
        $translator = Mockery::mock('Zend\I18n\Translator\Translator');

        $message = 'message';
        $textDomain = 'domain';
        $locale = 'fr';

        $translator->shouldReceive('translate')
            ->once()
            ->with($message, $textDomain, $locale)
            ->andReturn('translated message');

        $validator = new Translator($translator);

        $validator->translate($message, $textDomain, $locale);
    }

}
