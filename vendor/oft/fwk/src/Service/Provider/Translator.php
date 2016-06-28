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

namespace Oft\Service\Provider;

use Oft\Module\ModuleManager;
use Oft\Mvc\Application;
use Oft\Service\FactoryInterface;
use Oft\Service\ServiceLocatorInterface;
use Oft\Validator\Translator as Oft_Validator_Translator;
use Zend\I18n\Translator\Translator as Zend_Translator;
use Zend\Validator\AbstractValidator;

/**
 * Construit et configure le composant de traduction
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class Translator implements FactoryInterface
{
    /**
     * Langue par défaut
     * 
     * @var string
     */
    protected $defaultLanguage;
    
    /**
     * Langues autorisées avec la langue par défaut toujours présente en premier
     * 
     * @var array 
     */
    protected $possibleLanguages = array();

    /**
     * Langue utilisée
     * 
     * @var string
     */
    protected $language;

    /**
     * 
     * @param Application $app
     * @return Zend_Translator
     */
    public function create(ServiceLocatorInterface $app)
    {
        $translatorConfig = $app->config['translator'];

        $defaultLanguage = $this->getDefaultLanguage($translatorConfig);
        $possibleLanguages = $this->getPossibleLanguages($defaultLanguage, $translatorConfig);
        $language = $this->getLanguage($app, $defaultLanguage, $possibleLanguages);

        $translator = $this->getTranslatorFromModules($language, $app->moduleManager, $translatorConfig);

        $validatorTranslator = new Oft_Validator_Translator($translator);
        AbstractValidator::setDefaultTranslator($validatorTranslator);

        \Locale::setDefault($translator->getLocale());

        return $translator;
    }

    /**
     * Retourne une instance de Zend\I18n\Translator\Translator
     * configurée en fonction des modules chargés.
     *
     * @param string $language
     * @param ModuleManager $moduleManager
     * @param array $translatorConfig
     * @return Zend_Translator
     */
    public function getTranslatorFromModules($language, ModuleManager $moduleManager, $translatorConfig)
    {
        $factoryOptions = array(
            'locale' => $language,
            'translation_file_patterns' => array(),
            //'cache' => '' // Zend\Cache\StorageFactory
        );

        $modules = $moduleManager->getModules();
        $defaultModuleName =  $moduleManager->getDefault();
        
        foreach ($modules as $module) {
            $moduleName = $module->getName();
            $type = isset($translatorConfig[$moduleName]['type']) ? $translatorConfig[$moduleName]['type'] : $translatorConfig['default']['type'];
            $pattern = isset($translatorConfig[$moduleName]['pattern']) ? $translatorConfig[$moduleName]['pattern'] : $translatorConfig['default']['pattern'];

            $options = array(
                'type' => $type,
                'pattern' => $pattern,
                'base_dir' => $module->getDir('lang'),
            );
            
            if ($defaultModuleName == $moduleName) {
                $factoryOptions['translation_file_patterns'][] = $options;
            } else {
                array_unshift($factoryOptions['translation_file_patterns'], $options);
            }
        }
        
        return Zend_Translator::factory($factoryOptions);
    }

    public function getPossibleLanguages($defaultLanguage, $translatorConfig)
    {
        if (!isset($translatorConfig['availableLanguages']) || empty($translatorConfig['availableLanguages'])) {
            $possibleLanguages = array(
                $defaultLanguage
            );
        } else {
            $possibleLanguages = array_merge(
                array(
                    $defaultLanguage
                ),
                $translatorConfig['availableLanguages']
            );
            $possibleLanguages = array_unique($possibleLanguages);
        }

        return $possibleLanguages;
    }

    /**
     * Détection du langage :
     *  - Priorité à l'éventuel cookie de session (choix manuel de l'utilisateur)
     *  - Puis, utilisation de la valeur du profil (langue préférée)
     *  - Puis, utilisation de la valeur du navigateur
     *  - Sinon, utilisation de la locale par défaut définie en configuration
     *
     * @param Application $app
     * @param string $defaultLanguage
     * @param array $possibleLanguages
     * @return string
     */
    public function getLanguage($app, $defaultLang, array $possibleLanguages)
    {
        if ($app->isCli === true) {
            return $defaultLang;
        }

        // Prefered locale from cookies (identity or manual choice)
        $fromCookie = $app->http->request->getFromCookies('lang');
        if (in_array($fromCookie, $possibleLanguages)) {
            return $fromCookie;
        }

        // Prefered locale from browser
        $fromBrowser = $app->http->request->getPreferredLanguage($possibleLanguages);
        if (in_array($fromBrowser, $possibleLanguages)) {
            return $fromBrowser;
        }

        return $defaultLang;
    }

    public function getDefaultLanguage($translatorConfig)
    {
        return substr($translatorConfig['default']['locale'], 0, 2);
    }
}
