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

include_once __DIR__ . '/_files/functions.php';

use Oft\View\Helper\Footer;
use PHPUnit_Framework_TestCase;
use Zend\I18n\Translator\Translator;

class FooterTest extends PHPUnit_Framework_TestCase
{
    
    public function testMagicToString()
    {
        $appName = '{app-test}';
        $content = '{app-content}';
        $contact = array(
            'url' => '{app-contact-url}',
            'name' => '{app-contact-name}',
            'mail' => '{app-contact-mail}',
        );
        $links = array(
            'glyph' => array(
                'label' => 'Label',
                'title' =>  '%CONTACT_NAME%',
                'href' => '%CONTACT_URL%',
                'glyphicon' => 'envelope'
            ),
            'basic' => array(
                'label' => '%CONTACT_MAIL%',
                'href' => '%BASE_URL%/help',
            ),
            // False : méthode pour ignorer un lien existant dans le framework
            'ignored' => false,
        );

        $translator = new Translator();

        $view = \Mockery::mock('Zend\View\Renderer\RendererInterface');
        $view->shouldReceive('getBaseUrl')->andReturn('/');
        
        $footer = new Footer();
        $footer->setView($view);
        $footer->setTranslator($translator);
        $footer->setAppName($appName);
        $footer->setContact($contact);
        $footer->setLinks($links);
        $footer->addContent($content);
        
        $result = (string)$footer();

        $this->assertStringStartsWith('<footer>', trim($result));
        
        $this->assertTrue(strpos($result, $appName) !== false);
        $this->assertTrue(strpos($result, $content) !== false);
        $this->assertTrue(strpos($result, $contact['url']) !== false);
        $this->assertTrue(strpos($result, $contact['name']) !== false);
        $this->assertEquals(2, substr_count($result, '<li>'));
    }
    
}
