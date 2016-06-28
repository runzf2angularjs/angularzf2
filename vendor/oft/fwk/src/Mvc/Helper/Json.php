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

namespace Oft\Mvc\Helper;

class Json
{
    protected static $defaultOptions = array(
        'contentType' => 'application/json',
        'charset' => 'UTF-8',
        'statusCode' => 200,
        'serialize' => 'Zend_Json', // 'none' ou 'Zend_Json' (default)
        'serializeOptions' => array('enableJsonExprFinder' => true),
        'headers' => array()
    );

    public static function send($json, array $options = array())
    {
        $options = array_merge(self::$defaultOptions, $options);

        // Add encoding if text & missing from $options['contentType']
        if (stripos($options['contentType'], 'text/') === 0 && stripos($options['contentType'], 'charset') === false) {
            $options['contentType'] = $options['contentType'] .'; charset='.$charset;
        }

        // Headers
        $options['headers'] = array_merge(
            array(
                'Content-Type' => $options['contentType'],
            ),
            $options['headers']
        );

        // Encode
        switch ($options['serialize']) {
            default:
            case 'Zend_Json':
                $serialized = \Zend\Json\Json::encode($json, true, $options['serializeOptions']);
                break;
            case 'none':
                $serialized = $json;
                break;
        }

        // Passthru
        throw new \Oft\Mvc\Exception\HttpException(
            $options['statusCode'],
            $options['headers'],
            $serialized
        );
    }
}