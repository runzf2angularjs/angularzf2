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

use Oft\Entity\BaseEntity;

class Datagrid extends DatagridAbstract
{

    protected $link;

    /**
     *
     * @param array $options
     * @return boolean
     * @throws \RuntimeException
     */
    public function normalizeGridOptions(array $options)
    {
        $options = parent::normalizeGridOptions($options);

        // Initialisation du lien par défaut
        $defaultLink = array(
            'action' => null,
            'controller' => null,
            'module' => null,
            'params' => array(),
            'name' => null
        );

        // Récupération du lien
        if (isset($options['link']) && is_array($options['link'])) {
            $options['link'] = array_merge($defaultLink, $options['link']);

            $this->link = $this->view->smartUrl(
                $options['link']['action'], $options['link']['controller'], $options['link']['module'], $options['link']['params'], $options['link']['name']
            );
        }

        // Récupération de l'URL pour le sort
        if (!isset($options['orderLink'])) {
            $options['orderLink'] = $this->view->smartUrl();
        }

        // Récupération des colonnes devant être cliquables
        if (!isset($options['linkOn']) || empty($options['linkOn'])) {
            $options['linkOn'] = array();
        } elseif (is_string($options['linkOn'])) {
            $options['linkOn'] = array($options['linkOn']);
        } elseif (is_array($options['linkOn'])) {
            $options['linkOn'] = $options['linkOn'];
        } else {
            throw new \RuntimeException(
            "Options 'clickOn' invalide (" . get_class($this) . ')'
            );
        }

        return $options;
    }

    /**
     * Affichage d'un tableau basé sur des données fournies en paramètre.
     *
     * @param array  $idColumn           Colonne des données utilisée comme
     * @param \Iterator  $iterator           Données à afficher
     * @param array $columnsOptions     Entete de colonne avec parametres
     * @param array $options            Options d'affichage
     * @return string
     * @throws \RuntimeException
     */
    public function __invoke($idColumn, $iterator, array $columnsOptions, array $options = array())
    {
        $html = '';

        // Normalisation de columnOptions
        $columnsOptions = $this->normalizeColumnsOptions($columnsOptions);

        // Récupération / transformation des options par défaut
        $options = $this->normalizeGridOptions($options);

        // Création du paginateur
        $paginator = $this->getPaginator($iterator, $options);

        $html .= '<table'
            . (isset($options['id']) ? ' id="' . $options['id'] . '"' : '')
            . ' class="table table-bordered table-striped table-condensed"'
            . '>';
        $html .= '<thead>';
        $html .= '<tr>';

        // Affichage de l'entête
        $columnNumber = 0;
        foreach ($columnsOptions as $column => $columnData) {
            if ($column !== 'actions') {
                if (isset($columnData['visible']) && !$columnData['visible']) {
                    continue;
                }

                // width
                if (isset($columnData['width'])) {
                    $width = ' style="width:' . $columnData['width'] . '"';
                } else {
                    $width = '';
                }

                //Fleche et lien de sorting
                if (isset($columnData['sortable']) && $columnData['sortable']) {
                    if (isset($options['sort']) && $options['sort'] === $column) {
                        if ((isset($options['order']) && $options['order'] === 'asc') || !isset($options['order'])) {
                            $htmlOrder = 'desc';
                            $htmlRessource = '<span aria-hidden="true" class="glyphicon glyphicon-arrow-up"></span>';
                            $htmlRessourceAlt = $this->translator->translate('Sorting ascendant');
                        } elseif (isset($options['order']) && $options['order'] === 'desc') {
                            $htmlOrder = 'asc';
                            $htmlRessource = '<span aria-hidden="true" class="glyphicon glyphicon-arrow-down"></span>';
                            $htmlRessourceAlt = $this->translator->translate('Sorting descendant');
                        } else {
                            $htmlOrder = 'asc';
                            $htmlRessource = '<span aria-hidden="true" class="glyphicon glyphicon-sort"></span>';
                            $htmlRessourceAlt = $this->translator->translate('No sorting');
                        }
                    } else {
                        $htmlOrder = 'asc';
                        $htmlRessource = '<span aria-hidden="true" class="glyphicon glyphicon-sort"></span>';
                        $htmlRessourceAlt = $this->translator->translate('No sorting');
                    }

                    $orderLink = $options['orderLink'] . (strstr($options['orderLink'],'?') ? '&' : '?');
                    
                    $html .= '<th class="text-center"' . $width . '>'
                        . '<a href="' . $orderLink . 'sort=' . rawurlencode($column) . '&order=' . $htmlOrder
                        . '" title="' . $htmlRessourceAlt . '">'
                        . e($columnData['name']) . ' ' . $htmlRessource
                        . '</a>'
                        . '</th>';
                } else {
                    $html .= '<th class="text-center"' . $width . '>'
                        . e($columnData['name'])
                        . '</th>';
                }

                $columnNumber ++;
            }
        }

        if (count($options['actions'])) {
            $width = '';
            if (isset($columnsOptions['actions']) && isset($columnsOptions['actions']['width'])) {
                $width = ' style="width:' . $columnsOptions['actions']['width'] . '"';
            }

            $columnNumber++;
            $html .= '<th class="text-center"' . $width . '>'
                . e('Actions')
                . '</th>';
        }

        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        // Affichage des données
        foreach ($paginator as $row) {
            $html .= '<tr>';
            // transformation des données
            if($row instanceof BaseEntity) {
                $row = $row->getArrayCopy();
            }

            if (isset($options['callback'])) {
                // Passage par référence de l'enregistrement
                $row = call_user_func_array(
                    $options['callback'], array($row, $columnsOptions)
                );
            }

            // Affichage des données par colonne
            foreach ($columnsOptions as $column => $columnData) {
                if ($column !== 'actions') {
                    if (isset($columnData['visible']) && !$columnData['visible']) {
                        continue;
                    }

                    if (is_array($row) && !array_key_exists($column, $row)) {
                        throw new \RuntimeException("Colonne '$column' non définie");
                    }

                    // Alignement de la colonne
                    $alignClass = 'text-center';
                    if (isset($columnData['align']) && $columnData['align'] === 'right') {
                        $alignClass = 'text-right';
                    } elseif (isset($columnData['align']) && $columnData['align'] === 'left') {
                        $alignClass = 'text-left';
                    }

                    if(isset($columnData['display_as'])) {
                        switch ($columnData['display_as']) {
                            case 'datetime' :
                                $row[$column] = $this->view->dateFormatter($row[$column]);
                                break;
                            case 'date' :
                                $row[$column] = $this->view->dateFormatter($row[$column], null, 'none', 'sql', 'none');
                                break;
                            case 'time' :
                                $row[$column] = $this->view->dateFormatter($row[$column], 'none', 'medium', 'none', 'sql');
                                break;
                        }
                    }

                    $html .= '<td class="' . $alignClass . '">';
                    if (in_array($column, $options['linkOn'])) {
                        $lien = $this->link . '?' . $this->getLinkQueryString($idColumn, $row);
                        $html .= '<a href="' . $lien . '">' . e($row[$column]) . '</a>';
                    } else {
                        $html .= e($row[$column]);
                    }
                    $html .= '</td>';
                }
            }

            if (count($options['actions'])) {
                $html .= '<td class="text-center">';
                foreach ($options['actions'] as $action) {
                    $html .= $this->getActionColumn($action, $idColumn, $row);
                }
                $html .= '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';

        if ($paginator->count() > 1) {
            $urlPaginator = $options['orderLink'];
            if (isset($options['order']) && $options['sort'] !== null) {
                $urlPaginator .= (strstr($urlPaginator,'?') ? '&' : '?') . 'sort=' . $options['sort'] . '&order=' . $options['order'];
            }

            $paginatorControle = $this->view->paginationControl(
                $paginator, 'Sliding', 'oft/partials/sliding', array('url' => $urlPaginator)
            );

            $html .= '<tfoot>';
            $html .= '<tr>';
            $html .= '<td colspan="' . $columnNumber . '" align="center">' . $paginatorControle . '</td>';
            $html .= '</tr>';
            $html .= '</tfoot>';
        }

        $html .= '</table>';

        return $html;
    }

    /**
     *
     * @param type $idColumn
     * @param type $iterator
     * @param type $columnsOptions
     * @param array $options
     * @return array
     */
    public function getAjaxData($idColumn, $iterator, $columnsOptions, array $options)
    {
        return array();
    }

}
