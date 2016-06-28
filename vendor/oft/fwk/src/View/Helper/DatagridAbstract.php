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

use Zend\I18n\View\Helper\AbstractTranslatorHelper;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Adapter\Iterator;
use Zend\Paginator\Paginator;

abstract class DatagridAbstract extends AbstractTranslatorHelper
{
    /**
     * Normalise et valide les options de colonnes.
     *
     * @param  array $options
     * @return array
     */
    public function normalizeGridOptions(array $options)
    {
        if (isset($options['callback']) && !is_callable($options['callback'])) {
            throw new \RuntimeException(
                'Callback invalide pour le helper ('
                . get_class($this) . ')'
            );
        }

        if (!isset($options['pageRange'])) {
            $options['pageRange'] = 10;
        }

        if (!isset($options['page'])) {
            $options['page'] = 1;
        }

        if (!isset($options['itemPerPage'])) {
            $options['itemPerPage'] = 10;
        }

        if (!isset($options['actions'])) {
            $options['actions'] = array();
        }

        return $options;
    }

    /**
     * Retourne les options de colonnes normalisées.
     *
     * @param  array $columnsOptions
     * @return array $columnsOptions
     */
    public function normalizeColumnsOptions($columnsOptions)
    {
        if (is_array($columnsOptions)) {
            foreach($columnsOptions as $column => $columnData) {
                if (is_string($columnData)) {
                    $columnsOptions[$column] = array(
                        'name' => $columnData
                    );
                }
                if (is_array($columnData) && !isset($columnData['name'])) {
                    $columnsOptions[$column]['name'] = $column;
                }
            }
        }

        if (!count($columnsOptions)) {
            throw new \RuntimeException("DataGrid : Les options de colonnes doivent être fournies");
        }

        return $columnsOptions;
    }

    /**
     * Retourne les données formatées pour les appels Ajax.
     *
     * @param  string   $idColumn       Colonne identifiant des données
     * @param  Iterator $iterator       Données à traiter
     * @param  array    $columnsOptions Options de colonnes
     * @param  array    $options    Options de la grille
     * @return array
     */
    abstract public function getAjaxData($idColumn, $iterator, $columnsOptions, array $options);

    /**
     * Retourne un paginateur.
     *
     * @param  mixed  $iterator
     * @param  array  $options
     * @return Paginator
     */
    public function getPaginator($iterator, $options)
    {
        // Création du paginateur
        if ($iterator instanceof Paginator) {
            $paginator = $iterator;
        } else {
            $paginator = new Paginator(new ArrayAdapter($iterator));
        }

        $paginator->setPageRange($options['pageRange']);
        $paginator->setCurrentPageNumber($options['page']);
        $paginator->setItemCountPerPage($options['itemPerPage']);

        return $paginator;
    }

    /**
     *
     * @param type $actionOptions
     * @param type $row
     * @return string
     * @throws \RuntimeException
     */
    public function getActionColumn($actionOptions, $idColumn, $row)
    {
        $action = '';
        if (isset($actionOptions['content'])) {
            $action = $actionOptions['content'];
        } elseif (isset($actionOptions['image']) || isset($actionOptions['file'])) {
            $imageSrc = '';

            if (isset($actionOptions['image'])) {
                $imageSrc = $this->view->basepath() . $actionOptions['image'];
            }

            if (isset($actionOptions['file'])) {
                $imageSrc = $this->view->assets()->file($actionOptions['file']);
            }


            if (isset($actionOptions['alt'])) {
                $alt = e($actionOptions['alt'], true);
            } else {
                $alt = '';
            }

            $action = '<img alt="' . $alt . '" title="' . $alt . '" src="' . $imageSrc . '">';
        } else {
            throw new \RuntimeException("L'action n'est pas configurée correctement");
        }

        if (isset($actionOptions['link'])) {
            $defaultLink = array(
                'action'     => null,
                'controller' => null,
                'module'     => null,
                'params'     => array(),
                'name'     => null,
            );
            $link = array_merge($defaultLink, $actionOptions['link']);
            $link = $this->view->smartUrl(
                $link['action'],
                $link['controller'],
                $link['module'],
                $link['params'],
                $link['name']
            ) . '?' . $this->getLinkQueryString($idColumn, $row);

            $title = '';
            if (isset($actionOptions['title'])) {
                $title = ' title="' . $actionOptions['title'] . '"';
            } elseif (isset($actionOptions['alt'])) {
                $title = ' title="' . $actionOptions['alt'] . '"';
            }

            $action = '<a' . $title . ' href="' . $link . '">' . $action . '</a>';
        }

        return $action;
    }

    public function getLinkQueryString($idColumn, $row)
    {
        if (!is_array($idColumn)) {
            return $idColumn . '=' . $row[$idColumn];
        }

        // multi pk support
        $linkColumns = array();
        foreach ($idColumn as $linkColumn) {
            $linkColumns[] = $linkColumn . '=' . $row[$linkColumn];
        }

        return implode('&', $linkColumns);
    }
}
