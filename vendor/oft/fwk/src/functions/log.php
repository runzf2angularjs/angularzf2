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

/**
 * Ajoute un message au log
 *
 * @param string $message Message de la trace
 * @param array $context Informations complémentaires
 * @param string $channel Canal
 * @param int $level Niveau du log
 * @return void
 */
function oft_log($message, $context = array(), $channel = 'default', $level = \Monolog\Logger::NOTICE)
{
    try {
        $logger = \Monolog\Registry::getInstance($channel);
    } catch(\InvalidArgumentException $e) {
        // Trace to error_log() by default
        $logger = new \Monolog\Logger($channel);
        $logger->pushHandler(new Monolog\Handler\ErrorLogHandler());
        \Monolog\Registry::addLogger($logger, $channel);
    }

    $logger->log($level, $message, $context);
}

/**
 * Ajoute un message de niveau 'debug' au log
 *
 * @param string $message Message de la trace
 * @param array $context Informations complémentaires
 * @param string $channel Canal
 * @return void
 */
function oft_debug($message, $context = array(), $channel = 'default')
{
    oft_log($message, $context, $channel, \Monolog\Logger::DEBUG);
}

/**
 * Ajoute un message de niveau 'info' au log
 *
 * @param string $message Message de la trace
 * @param array $context Informations complémentaires
 * @param string $channel Canal
 * @return void
 */
function oft_info($message, $context = array(), $channel = 'default')
{
    oft_log($message, $context, $channel, \Monolog\Logger::INFO);
}

/**
 * Ajoute un message de niveau 'notice' au log
 *
 * @param string $message Message de la trace
 * @param array $context Informations complémentaires
 * @param string $channel Canal
 * @return void
 */
function oft_notice($message, $context = array(), $channel = 'default')
{
    oft_log($message, $context, $channel, \Monolog\Logger::NOTICE);
}

/**
 * Ajoute un message de niveau 'warning' au log
 *
 * @param string $message Message de la trace
 * @param array $context Informations complémentaires
 * @param string $channel Canal
 * @return void
 */
function oft_warning($message, $context = array(), $channel = 'default')
{
    oft_log($message, $context, $channel, \Monolog\Logger::WARNING);
}

/**
 * Ajoute un message de niveau 'error' au log
 *
 * @param string $message Message de la trace
 * @param array $context Informations complémentaires
 * @param string $channel Canal
 * @return void
 */
function oft_error($message, $context = array(), $channel = 'default')
{
    oft_log($message, $context, $channel, \Monolog\Logger::ERROR);
}

/**
 * Ajoute un message de niveau 'critical' au log
 *
 * @param string $message Message de la trace
 * @param array $context Informations complémentaires
 * @param string $channel Canal
 * @return void
 */
function oft_critical($message, $context = array(), $channel = 'default')
{
    oft_log($message, $context, $channel, \Monolog\Logger::CRITICAL);
}

/**
 * Ajoute un message de niveau 'emergency' au log
 *
 * @param string $message Message de la trace
 * @param array $context Informations complémentaires
 * @param string $channel Canal
 * @return void
 */
function oft_emergency($message, $context = array(), $channel = 'default')
{
    oft_log($message, $context, $channel, \Monolog\Logger::EMERGENCY);
}

/**
 * Ajoute une exception aux logs
 *
 * @param Exception $e Exception
 * @param array $context Informations complémentaires
 * @param string $channel Canal
 * @param int $level Niveau de la trace
 */
function oft_exception(Exception $e, $context = array(), $channel = 'default', $level = \Monolog\Logger::WARNING)
{
    $message = "Exception '" . get_class($e) . "' with message '" . $e->getMessage() . "'\n"
        . "Trace : \n" . $e->getTraceAsString() . "\n";

    if ($e->getPrevious() != null) {
        $pe = $e->getPrevious();
        $message .= "Previous exception '" . get_class($pe) . "' with message '" . $pe->getMessage() . "'\n"
        . "Trace : \n" . $pe->getTraceAsString() . "\n";
    }

    oft_log($message, $context, $channel, $level);
}

/**
 * Ajoute une trace de sécurité dans les logs via le canal 'security'
 *
 * @param string $message Message de la trace
 * @param array $context Informations complémentaires
 * @param int $level Niveau du log
 * @return void
 */
function oft_trace($message, $context = array(), $level = \Monolog\Logger::INFO)
{
    $app = \Oft\Util\Functions::getApp();

    /* @var $identity Oft\Auth\Identity */
    $identity = $app->identity->get();

    oft_log('[' . $identity->getUsername() . '] ' . $message, $context, 'security', $level);
}
