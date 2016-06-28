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

namespace Oft\Util;

class ErrorHandler
{

    /**
     * Flag : conversion des "notices" en "exceptions"
     *
     * @var bool
     */
    protected static $convertNoticeToException;

    /**
     * Gestionnaire d'erreurs
     *
     * @param int $level Niveau de l'erreur levée
     * @param string $message Message d'erreur
     * @param string $file Fichier dans lequel l'erreur a été levée
     * @param int $line Numéro de ligne dans laquelle l'erreur a été levée
     * @throws \ErrorException
     * @return mixed
     */
    public static function handle($level, $message, $file, $line)
    {
        // Respecter le fait que l'error_reporting a été désactiver
        if (!error_reporting()) {
            return false;
        }

        switch ($level) {
            case E_NOTICE :
            case E_USER_NOTICE :
                if (self::$convertNoticeToException) {
                    break;
                }
            case E_DEPRECATED :
            case E_USER_DEPRECATED :
                return false;
        }

        throw new \ErrorException($message, 0, $level, $file, $line);
    }

    /**
     * Enregistre le gestionnaire d'erreur
     *
     * @param bool $convertNoticeToException
     * @return mixed
     */
    public static function register($convertNoticeToException = true)
    {
        self::$convertNoticeToException = $convertNoticeToException;

        return set_error_handler(array(__CLASS__, 'handle'));
    }

}
