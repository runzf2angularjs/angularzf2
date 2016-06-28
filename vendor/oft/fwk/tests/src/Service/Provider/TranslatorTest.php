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

namespace Oft\Test\Service\Provider;

use Locale;
use Mockery;
use Oft\Module\ModuleManager;
use Oft\Service\Provider\Translator;
use Oft\Test\Mock\ApplicationMock;
use PHPUnit_Framework_TestCase;
use ReflectionObject;
use Zend\Validator\AbstractValidator;

class TranslatorTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        AbstractValidator::setDefaultTranslator(null);
    }
    
    protected function tearDown()
    {
        AbstractValidator::setDefaultTranslator(null);
    }
    
    protected function getApp(array $config = array(), array $identity = array(), $moduleManager = null)
    {
        return ApplicationMock::factory($config, $identity, $moduleManager);
    }

    public function testGetDefaultLanguage()
    {
        $config = array(
            'default' => array(
                'locale' => 'fr',
            )
        );

        $translator = new Translator;
        $lang = $translator->getDefaultLanguage($config);

        $this->assertSame('fr', $lang);
    }

    public function testGetDefaultLanguageTruncateAboveTwo()
    {
        $config = array(
            'default' => array(
                'locale' => 'fr_FR',
            )
        );

        $translator = new Translator;
        $lang = $translator->getDefaultLanguage($config);

        $this->assertSame('fr', $lang);
    }

    public function testGetLanguageInCli()
    {
        $config = array('cli' => true);

        $app = $this->getApp($config);

        $translator = new Translator;
        $lang = $translator->getLanguage($app, 'default', array());

        $this->assertSame('default', $lang);
    }

    public function testGetLanguageInCookie()
    {
        $possibleLanguages = array('fr', 'en', 'de');

        $app = $this->getApp();

        $app->http->request->shouldReceive('getFromCookies')
            ->with('lang')
            ->andReturn('de'); // Lang in cookie

        $translator = new Translator;
        $lang = $translator->getLanguage($app, 'default', $possibleLanguages);

        $this->assertSame('de', $lang);
    }

    public function testGetLanguageInBrowser()
    {
        $possibleLanguages = array('fr', 'en', 'de');
        $config = array('cli' => false);
        $identity = array(/* Lang NOT in user identity */);

        $app = $this->getApp($config, $identity);

        $app->http->request->shouldReceive('getFromCookies')
            ->with('lang')
            ->andReturn(null); // Lang NOT in cookie

        $app->http->request->shouldReceive('getPreferredLanguage')
            ->with($possibleLanguages)
            ->andReturn('de'); // Lang in HTTP request (browser)

        $translator = new Translator;
        $lang = $translator->getLanguage($app, 'default', $possibleLanguages);

        $this->assertSame('de', $lang);
    }

    public function testGetPossibleLanguagesWithEmptyAvailableLanguages()
    {
        $translatorConfig = array(
            'availableLanguages' => array()
        );
        $translator = new Translator;
        $possibleLanguages = $translator->getPossibleLanguages('fr', $translatorConfig);

        $this->assertSame(array('fr'), $possibleLanguages);
    }

    public function testGetPossibleLanguagesWithUnsetAvailableLanguages()
    {
        $translatorConfig = array(
            //'availableLanguages' => array()
        );
        $translator = new Translator;
        $possibleLanguages = $translator->getPossibleLanguages('fr', $translatorConfig);

        $this->assertSame(array('fr'), $possibleLanguages);
    }
    
    public function testGetPossibleLanguagesWithManyAvailableLanguages()
    {
        $translatorConfig = array(
            'availableLanguages' => array('fr', 'en')
        );
        $translator = new Translator;
        $possibleLanguages = $translator->getPossibleLanguages('fr', $translatorConfig);

        $this->assertSame(array('fr', 'en'), array_values($possibleLanguages));
    }

    public function testGetTranslatorFromModules()
    {
        $moduleManager = new ModuleManager();

        $module1 = Mockery::mock('Oft\Module\ModuleInterface');
        $module1->shouldReceive('getName')->andReturn('module1');
        $module1->shouldReceive('getDir')->with('lang')->andReturn('/path/to/module1/lang');
        
        $module2 = Mockery::mock('Oft\Module\ModuleInterface');
        $module2->shouldReceive('getName')->andReturn('module2');
        $module2->shouldReceive('getDir')->with('lang')->andReturn('/path/to/module2/lang');

        $module3 = Mockery::mock('Oft\Module\ModuleInterface');
        $module3->shouldReceive('getName')->andReturn('module3');
        $module3->shouldReceive('getDir')->with('lang')->andReturn('/path/to/module3/lang');

        $moduleManager->addModule($module3, true);
        $moduleManager->addModules(array(
            $module2,
            $module1
        ));

        $translatorConfig = array(
            'default' => array(
                'type' => 'php',
                'pattern' => '%s.php'
            ),
            'module2' => array(
                'type' => 'ini',
                'pattern' => 'langFile_%s.ini'
            )
        );

        $translatorFactory = new Translator;

        $translator = $translatorFactory->getTranslatorFromModules('fr', $moduleManager, $translatorConfig);

        $this->assertSame('fr', $translator->getLocale());

        $reflectionTranslate = new ReflectionObject($translator);
        $reflectionPattern = $reflectionTranslate->getProperty('patterns');
        $reflectionPattern->setAccessible(true);
        $patterns = $reflectionPattern->getValue($translator);

        $this->assertCount(1, $patterns);
        $this->assertArrayHasKey('default', $patterns);
        $this->assertCount(3, $patterns['default']);

        // From defaults
        $this->assertEquals('php', $patterns['default'][0]['type']);
        $this->assertEquals('/path/to/module1/lang', $patterns['default'][0]['baseDir']);
        $this->assertEquals('%s.php', $patterns['default'][0]['pattern']);

        // From specific
        $this->assertEquals('ini', $patterns['default'][1]['type']);
        $this->assertEquals('/path/to/module2/lang', $patterns['default'][1]['baseDir']);
        $this->assertEquals('langFile_%s.ini', $patterns['default'][1]['pattern']);
        
        // Default Module in Last
        $this->assertEquals('php', $patterns['default'][2]['type']);
        $this->assertEquals('/path/to/module3/lang', $patterns['default'][2]['baseDir']);
        $this->assertEquals('%s.php', $patterns['default'][2]['pattern']);
    }

    public function testCreateService()
    {
        $config = array(
            'translator' => array(
                'availableLanguages' => array(
                    'en'
                ),
                'default' => array(
                    'locale' => 'fr',
                    'type' => 'phparray',
                    'pattern' => '%s.php',
                ),
            )
        );

        $currentLocale = Locale::setDefault('fr');
        
        $app = $this->getApp($config);

        $app->http->request->shouldReceive('getPreferredLanguage')
            ->with(array('fr', 'en'))
            ->andReturn('en');

        $app->http->request->shouldReceive('getFromCookies')
            ->with('lang')
            ->andReturn('en');

        $translatorProvider = new Translator();
        $translator = $translatorProvider->create($app);

        $this->assertInstanceOf('Zend\I18n\Translator\Translator', $translator);
        $this->assertSame('en', $translator->getLocale());

        $validatorTranslator = AbstractValidator::getDefaultTranslator();

        $reflectionTranslate = new ReflectionObject($validatorTranslator);
        $reflectionPattern = $reflectionTranslate->getProperty('translator');
        $reflectionPattern->setAccessible(true);

        $newLocale = Locale::getDefault();

        $this->assertNotSame($currentLocale, $newLocale);
        $this->assertSame('en', $newLocale);
        $this->assertSame($translator, $reflectionPattern->getValue($validatorTranslator));
    }
}
