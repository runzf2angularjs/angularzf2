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

namespace Oft\Debug\Service;

use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DebugBar;
use Exception;
use Oft\Debug\DebugInterface;

/**
 * Service de la barre de debug
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class Debug implements DebugInterface
{

    /**
     * @var DebugBar
     */
    protected $debugBar;

    /**
     * Défini l'objet DebugBar
     * 
     * @param DebugBar $debugBar
     */
    public function setDebugBar(DebugBar $debugBar)
    {
        $this->debugBar = $debugBar;
    }

    /**
     * Ajoute une exception dans la barre de debug
     * 
     * @param Exception $e
     */
    public function addException(Exception $e)
    {        
        $this->debugBar['exceptions']->addException($e);
    }
    
    /**
     * Ajoute un message dans la barre de debug
     * 
     * @param string $message
     * @param string $type
     */
    public function addMessage($message, $type = 'messages')
    {
        if (! $this->debugBar->hasCollector($type)) {
            $messagesCollector = new MessagesCollector($type);
            
            $this->debugBar->addCollector($messagesCollector);
        }
        
        $this->debugBar[$type]->addMessage($message);
    }

    /**
     * Retourne vrai si le mode debug est actif
     *
     * Toujours vrai dans ce contexte
     *
     * @return boolean
     */
    public function isDebug()
    {
        return true;
    }

    /**
     * Affiche le résultat de "print_r" sur la variable donnée
     *
     * @param type $var Variable à afficher
     * @param type $title Titre facultatif de l'affichage
     * @param type $return Si VRAI, retourne le résultat au lieu de l'afficher
     * @return string
     */
    public function dump($var, $title = null, $return = false)
    {
        $content = '';
        if (is_string($title)) {
            $content .= '<strong>' . $title . '</strong>';
        }
        $content .= '<pre>' . print_r($var, true) . '</pre>';

        if ($return) {
            return $content;
        }

        echo $content;
    }

}
