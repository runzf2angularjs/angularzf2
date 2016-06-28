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

namespace Oft\View\Helper;

use Zend\View\Helper\AbstractHelper;

class Assets extends AbstractHelper
{

    /**
     * Constantes de placement
     */
    const APPEND = 'append';
    const PREPEND = 'prepend';

    /**
     * Tableau des collections à charger AVANT les assets du framework
     *
     * @var array
     */
    public static $prepend = array();

    /**
     * Tableau des collections à charger APRES les assets du framework
     *
     * @var array
     */
    public static $append = array();

    /**
     * Configuration
     *
     * @var array
     */
    protected $configuration;

    /**
     * Tableau des collections
     *
     * @var array
     */
    public static $collections = array();

    /**
     * Tableau de stockage des éléments traités pour la sécurité anti-bouclage
     *
     * @var array
     */
    public static $added = array();

    /**
     * Méthode principale de l'aide de vue
     *
     * @param string $collections
     * @return self
     */
    public function __invoke($collections = null)
    {
        if (\is_string($collections)) {
            $this->add($collections);
        }

        return $this;
    }

    /**
     * Définit la configuration
     *
     * @param array $configuration
     * @return self
     */
    public function setConfiguration(array $configuration)
    {
        $this->configuration = $configuration;

        return $this;
    }

    /**
     * Retourne la configuration
     *
     * @return array
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Ajoute les collections par défaut définies en configuration
     *
     * @return void
     */
    public function addDefaults()
    {
        $defaults = $this->configuration['defaults'];
        foreach ($defaults as $name) {
            $this->add(\ltrim($name, '@'));
        }

        return $this;
    }

    /**
     * Ajoute les collections associées à la collection donnée
     *
     * Gère les références aux autres collections et l'anti-bouclage
     *
     * @param string $name
     * @return self
     */
    public function add($name)
    {
        // Anti-bouclage
        if (\in_array($name, self::$added)) {
            return $this;
        }
        self::$added[] = $name;

        // Ajout de la collection
        $collections = $this->configuration['collections'];
        foreach ($collections[$name]['assets'] as $index => $collection) {
            // Gestion d'une référence
            if (\is_string($collection) && $collection[0] === '@') {
                $this->add(ltrim($collection, '@'));
                continue;
            }

            // Tableau d'assets : pas de traitement si aucun fichier défini
            if (!isset($collection['files']) || empty($collection['files'])) {
                continue;
            }

            $url = $this->getCollectionUrl(
                $this->getVersion(),
                $name,
                $collection['type'],
                $this->getCollectionHash($collection['files']),
                $index
            );

            $tag = isset($collection['tag']) ? $collection['tag'] : array();

            self::$collections[] = array(
                'url' => $url,
                'type' => $collection['type'],
                'tag' => $tag,
            );
        }

        return $this;
    }

    /**
     * Ajoute une feuille de style
     *
     * @param string $file Chemin vers le fichier à partir de la racine web
     * @param string $placement Placement du fichier (avant ou après les collections du framework)
     * @param array $tag Paramètres passés aux aides de vue ZF
     */
    public function addStyle($file, $placement = self::APPEND, array $tag = array())
    {
        self::${$placement}[] = array(
            'url' => $this->view->basePath($file),
            'type' => 'css',
            'tag' => $tag,
        );
    }

    /**
     * Ajoute un fichier JavaScript
     *
     * @param string $file Chemin vers le fichier à partir de la racine web
     * @param string $placement Placement du fichier (avant ou après les collections du framework)
     * @param array $tag Paramètres passés aux aides de vue ZF
     */
    public function addScript($file, $placement = self::APPEND, array $tag = array())
    {
        self::${$placement}[] = array(
            'url' => $this->view->basePath($file),
            'type' => 'js',
            'tag' => $tag,
        );
    }

    /**
     * Génère l'URL d'une collection à partir de ses informations
     *
     * @param int $version Version du média
     * @param string $name Nom de la collection
     * @param string $type Type de la collection
     * @param string $hash Hash unique de la collection
     * @param string $index Index de la collection
     * @return string
     */
    public function getCollectionUrl($version, $name, $type, $hash, $index)
    {
        return $this->view->getBaseUrl() . '/assets' .
            '/v' . $version .
            '/' . $name .
            '/' . $type .
            '/' . $hash . $index .
            '.' . $type ;
    }

    /**
     * Génère le hash d'une collection, à partir des chemins vers les fichiers
     *
     * @param array $files
     * @return string
     */
    public function getCollectionHash($files)
    {
        return \substr(\md5(\implode(';', $files)), 0, 7);
    }

    /**
     * Retourne la version des collections
     *
     * @return mixed
     */
    public function getVersion()
    {
        return $this->configuration['options']['version'];
    }

    /**
     * Retourne directement l'URL pour un fichier
     *
     * @param string $path
     * @return string
     */
    public function file($path)
    {
        $path = \ltrim($path, '/');
        $baseUrl = $this->view->getBaseUrl();

        return $baseUrl . '/assets' .
            '/v' . $this->getVersion() .
            '/' . $path;
    }

    /**
     * Alimente les aides de vues ZF
     *
     */
    public function finalize()
    {
        // Ajout des collections par défaut
        $this->addDefaults();

        // Ajout des collections "prepend"
        foreach (self::$prepend as $collection) {
            \array_unshift(self::$collections, $collection);
        }

        // Ajout des collections "append"
        foreach (self::$append as $collection) {
            \array_push(self::$collections, $collection);
        }

        // Ajout aux aides de vue ZF
        foreach (self::$collections as $collection) {
            switch ($collection['type']) {
                case 'js':
                    $this->finalizeJs($collection);
                    break;
                case 'css':
                    $this->finalizeCss($collection);
                    break;
            }
        }
    }

    /**
     * Alimente l'aide de vue ZF pour JavaScript
     *
     * @param array $collection
     */
    public function finalizeJs(array $collection)
    {
        $default = array(
            'type' => 'text/javascript',
            'attrs' => array(),
        );

        $tag = \array_merge($default, $collection['tag']);

        $this->view
            ->headScript()
            ->appendFile(
                $collection['url'],
                $tag['type'],
                $tag['attrs']
            );
    }

    /**
     * Alimente l'aide de vue ZF pour les feuilles de styles
     *
     * @param array $collection
     */
    public function finalizeCss(array $collection)
    {
        $default = array(
            'media' => 'screen',
            'conditional' => null,
            'extras' => array(),
        );

        $tag = \array_merge($default, $collection['tag']);

        $this->view
            ->headLink()
            ->appendStylesheet(
                $collection['url'],
                $tag['media'],
                $tag['conditional'],
                $tag['extras']
            );
    }

}
