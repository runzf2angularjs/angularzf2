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

class Footer extends AbstractTranslatorHelper
{
    protected $appName = '';
    protected $contact = array(
        'url' => null,
        'name' => null,
    );
    protected $links = array();
    protected $content = array();

    /**
     * Retourne le code HTML du pied de page
     *
     * Cette aide se base sur la configuration pour les éléments suivants :
     * - Nom de l'application
     * - Contact : URL et nom
     *
     * @todo Plan du site
     * @todo IHM d'aide
     * @return string
     */
    public function __invoke()
    {
        return $this;
    }

    public function setAppName($appName)
    {
        $this->appName = $appName;

        return $this;
    }

    public function setContact(array $contact)
    {
        $this->contact = $contact;

        return $this;
    }

    public function setLinks(array $links)
    {
        $this->links = $links;

        return $this;
    }

    public function getPreparedLinks()
    {
        $links = array();
        foreach ($this->links as $link) {
            
            // Pas un tableau : ignoré
            if (!is_array($link)) {
                continue;
            }

            $links[] = array_merge(
                array(
                    'href' => '#',
                    'label' => '',
                    'title' => $link['label'],
                    'glyphicon' => '',
                ),
                array_map(array($this, 'linkReplace'), $link)
            );
        }

        return $links;
    }

    protected function linkReplace($string)
    {
        if (\strpos($string, '%') === false) {
            return $string;
        }

        $replacements = array(
            '%BASE_URL%' => $this->view->getBaseUrl(),
            '%CONTACT_URL%' => $this->contact['url'],
            '%CONTACT_NAME%' => $this->contact['name'],
            '%CONTACT_MAIL%' => $this->contact['mail'],
        );

        return \str_replace(
            \array_keys($replacements),
            \array_values($replacements),
            $string
        );
    }

    public function addContent($content)
    {
        $this->content[] = $content;
    }

    public function __toString()
    {
        $translator = $this->getTranslator();
        $links = $this->getPreparedLinks();

        $html = '
            <footer>
                <p class="text-center">
                    ' . e($translator->translate($this->appName)) . '
                </p>
        ';

        $list = '';
        foreach ($links as $link) {            
            // Glyphicon
            $glyphicon = '';
            if (!empty($link['glyphicon'])) {
                $glyphicon = '<span class="glyphicon glyphicon-' . e($link['glyphicon']) . '"></span>';
            }

            $list .= '
                        <li>
                            <a
                                href="' . e($link['href']) . '"
                                title="' . e($translator->translate($link['title'])) . '">
                                ' . $glyphicon . ' ' . e($translator->translate($link['label'])) . '
                            </a>
                        </li>
            ';
        }

        if(!empty($list)) {
            $html .= '
                <div class="container">
                    <ul class="nav nav-pills nav-justified">
                        ' . $list . '
                    </ul>
                </div>
            ';
        }

        $html .= '
            </footer>
        ';

        foreach ($this->content as $content) {
            $html .= (string)$content;
        }

        return $html;
    }

}
