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

use ArrayObject;
use Zend\I18n\View\Helper\AbstractTranslatorHelper;

class FlashMessenger extends AbstractTranslatorHelper
{

    /**
     * Message de type "succès"
     *
     * @const string
     */
    const SUCCESS = 'success';

    /**
     * Message de type "information"
     *
     * @const string
     */
    const INFO = 'info';

    /**
     * Message de type "avertissement"
     *
     * @const string
     */
    const WARNING = 'warning';

    /**
     * Message de type "erreur" ou "danger"
     *
     * @const string
     */
    const DANGER = 'danger';

    /**
     * Elément de stockage des messages
     *
     * @var ArrayObject
     */
    protected static $sessionContainer;

    /**
     * Définit le conteneur de messages
     *
     * @param mixed $storage
     */
    public static function setMessageContainer($storage)
    {
        self::$sessionContainer = $storage;
    }

    /**
     * Retourne le conteneur de messages
     *
     * @return self::$sessionContainer
     */
    public static function getMessagesContainer()
    {
        if (self::$sessionContainer === null) {
            if (!isset($_SESSION[__CLASS__])) {
                $_SESSION[__CLASS__] = new ArrayObject();
            }
            self::$sessionContainer = $_SESSION[__CLASS__];
        }

        return self::$sessionContainer;
    }

    /**
     * Retourne le code HTML des messages flash
     *
     * Ajoute un message flash si le texte donné est non-nul
     *
     * @param string $text Texte du message à ajouter
     * @param string $type Type de message à ajouter
     * @return string
     */
    public function __invoke($text = null, $type = self::INFO)
    {
        if ($text !== null) {
            self::getMessagesContainer()->append(array(
                $type,
                $text
            ));
            return;
        }

        $content = array();
        $messages = self::getMessagesContainer()->exchangeArray(array());
        foreach ($messages as $message) {
            $content[] =
                '<div class="alert alert-dismissable alert-' . e($message[0]) . '">' .
                    '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">' .
                        '&times;' .
                    '</button>' .
                    e($this->getTranslator()->translate($message[1])) .
                '</div>';
        }

        return implode('', $content);
    }

}
